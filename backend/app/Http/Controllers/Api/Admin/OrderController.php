<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name,email,phone', 'items.product:id,name,thumbnail'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('order_code', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%"));
            }))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->per_page ?? 15),
        ]);
    }

    public function show($id)
    {
        $order = Order::with([
            'user:id,name,email,phone,address',
            'items.product:id,name,thumbnail,sku',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $order = Order::with('items')->findOrFail($id);
        $oldStatus = $order->status;

        Log::info('Admin requested order status update.', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'user_id' => $order->user_id,
        ]);

        if (in_array($order->status, ['cancelled', 'refunded'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi trạng thái đơn hàng đã hủy.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            if (in_array($request->status, ['cancelled', 'refunded'])) {
                $order->restoreStock();
            }

            $updates = ['status' => $request->status];

            if ($request->status === 'delivered' && $order->payment_method === 'cod') {
                $updates['payment_status'] = 'paid';
            }

            if ($request->status === 'refunded') {
                $updates['payment_status'] = 'refunded';
            }

            $order->update($updates);
        });

        $order = $order->fresh(['items.product:id,name,thumbnail,sku', 'user:id,name,email,phone,address']);
        $mailSent = false;
        $mailError = null;

        if ($order->user?->email && $oldStatus !== $order->status) {
            try {
                $this->sendOrderStatusEmail($order, $oldStatus);
                $mailSent = true;

                Log::info('Order status email sent.', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status,
                    'user_email' => $order->user->email,
                ]);
            } catch (Throwable $e) {
                $mailError = 'Cập nhật trạng thái thành công nhưng gửi email thất bại.';

                Log::error('Failed to send order status email.', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'user_email' => $order->user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::info('Order status email skipped.', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
                'user_email' => $order->user?->email,
                'reason' => !$order->user?->email ? 'missing_user_email' : 'status_not_changed',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $mailError ?: 'Cập nhật trạng thái đơn hàng thành công!',
            'mail_sent' => $mailSent,
            'data' => $order,
        ]);
    }

    private function sendOrderStatusEmail(Order $order, string $oldStatus): void
    {
        $user = $order->user;
        $newStatusLabel = $this->statusLabel($order->status);
        $oldStatusLabel = $this->statusLabel($oldStatus);
        $itemsHtml = $order->items->map(function ($item) {
            $name = e($item->product_name ?: $item->product?->name ?: 'Sản phẩm');
            $sku = e($item->product_sku ?: $item->product?->sku ?: 'N/A');
            $quantity = (int) $item->quantity;
            $unitPrice = $this->formatMoney($item->unit_price);
            $totalPrice = $this->formatMoney($item->total_price);

            return "
                <tr>
                    <td style='padding:12px;border-bottom:1px solid #e5e7eb;'>
                        <strong style='color:#111827;'>{$name}</strong>
                        <div style='color:#6b7280;font-size:12px;margin-top:4px;'>SKU: {$sku}</div>
                    </td>
                    <td style='padding:12px;border-bottom:1px solid #e5e7eb;text-align:center;'>{$quantity}</td>
                    <td style='padding:12px;border-bottom:1px solid #e5e7eb;text-align:right;'>{$unitPrice}</td>
                    <td style='padding:12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;'>{$totalPrice}</td>
                </tr>
            ";
        })->implode('');

        $customerName = e($user->name ?: $order->shipping_name ?: 'Quý khách');
        $orderCode = e($order->order_code);
        $shippingName = e($order->shipping_name);
        $shippingPhone = e($order->shipping_phone);
        $shippingAddress = e(trim($order->shipping_address . ', ' . $order->shipping_city, ' ,'));
        $paymentMethod = e($this->paymentMethodLabel($order->payment_method));
        $paymentStatus = e($this->paymentStatusLabel($order->payment_status));
        $createdAt = optional($order->created_at)->format('d/m/Y H:i');
        $subject = "Laptop Shop - Don hang {$order->order_code} da cap nhat trang thai";
        $plainText = "Xin chao {$user->name},\n\n"
            . "Don hang {$order->order_code} cua ban da duoc cap nhat trang thai tu "
            . "{$oldStatusLabel} sang {$newStatusLabel}.\n\n"
            . "Tong thanh toan: {$this->formatMoney($order->final_amount)}\n"
            . "Nguoi nhan: {$order->shipping_name}\n"
            . "So dien thoai: {$order->shipping_phone}\n"
            . "Dia chi: " . trim($order->shipping_address . ', ' . $order->shipping_city, ' ,') . "\n"
            . "Thanh toan: {$this->paymentMethodLabel($order->payment_method)} - {$this->paymentStatusLabel($order->payment_status)}\n\n"
            . "Cam on ban da mua sam tai Laptop Shop.";

        Mail::send([], [], function ($message) use (
            $user,
            $customerName,
            $orderCode,
            $oldStatusLabel,
            $newStatusLabel,
            $itemsHtml,
            $shippingName,
            $shippingPhone,
            $shippingAddress,
            $paymentMethod,
            $paymentStatus,
            $createdAt,
            $order,
            $subject,
            $plainText
        ) {
            $message->to($user->email, $user->name)
                ->subject($subject)
                ->text($plainText)
                ->html("
                    <div style='font-family:Arial,sans-serif;max-width:720px;margin:auto;padding:30px;color:#111827;'>
                        <h2 style='color:#2563eb;margin:0 0 16px;'>Cập nhật trạng thái đơn hàng</h2>
                        <p>Xin chào <strong>{$customerName}</strong>,</p>
                        <p>Đơn hàng <strong>{$orderCode}</strong> của bạn vừa được cập nhật trạng thái:</p>

                        <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px;margin:22px 0;'>
                            <div style='font-size:13px;color:#6b7280;margin-bottom:6px;'>Trạng thái</div>
                            <div style='font-size:18px;font-weight:700;color:#1d4ed8;'>{$oldStatusLabel} → {$newStatusLabel}</div>
                        </div>

                        <h3 style='margin:24px 0 10px;color:#111827;'>Chi tiết đơn hàng</h3>
                        <table style='width:100%;border-collapse:collapse;border:1px solid #e5e7eb;font-size:14px;'>
                            <thead>
                                <tr style='background:#f9fafb;color:#374151;'>
                                    <th style='padding:12px;text-align:left;border-bottom:1px solid #e5e7eb;'>Sản phẩm</th>
                                    <th style='padding:12px;text-align:center;border-bottom:1px solid #e5e7eb;'>SL</th>
                                    <th style='padding:12px;text-align:right;border-bottom:1px solid #e5e7eb;'>Đơn giá</th>
                                    <th style='padding:12px;text-align:right;border-bottom:1px solid #e5e7eb;'>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>{$itemsHtml}</tbody>
                        </table>

                        <div style='margin-top:18px;border-top:1px solid #e5e7eb;padding-top:14px;font-size:14px;'>
                            <p style='margin:6px 0;'><strong>Tạm tính:</strong> {$this->formatMoney($order->total_amount)}</p>
                            <p style='margin:6px 0;color:#16a34a;'><strong>Giảm giá:</strong> -{$this->formatMoney($order->discount_amount)}</p>
                            <p style='margin:8px 0;font-size:18px;color:#2563eb;'><strong>Tổng thanh toán:</strong> {$this->formatMoney($order->final_amount)}</p>
                        </div>

                        <div style='display:block;background:#f9fafb;border-radius:10px;padding:16px;margin-top:20px;font-size:14px;'>
                            <p style='margin:6px 0;'><strong>Người nhận:</strong> {$shippingName}</p>
                            <p style='margin:6px 0;'><strong>Số điện thoại:</strong> {$shippingPhone}</p>
                            <p style='margin:6px 0;'><strong>Địa chỉ:</strong> {$shippingAddress}</p>
                            <p style='margin:6px 0;'><strong>Thanh toán:</strong> {$paymentMethod} - {$paymentStatus}</p>
                            <p style='margin:6px 0;'><strong>Ngày đặt:</strong> {$createdAt}</p>
                        </div>

                        <p style='color:#6b7280;font-size:13px;margin-top:24px;'>Cảm ơn bạn đã mua sắm tại Laptop Shop.</p>
                    </div>
                ");
        });
    }

    private function statusLabel(string $status): string
    {
        return [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
        ][$status] ?? $status;
    }

    private function paymentMethodLabel(?string $method): string
    {
        return [
            'cod' => 'Thanh toán khi nhận hàng',
            'vnpay' => 'VNPay',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
        ][$method] ?? ($method ?: 'N/A');
    }

    private function paymentStatusLabel(?string $status): string
    {
        return [
            'unpaid' => 'Chưa thanh toán',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
        ][$status] ?? ($status ?: 'N/A');
    }

    private function formatMoney($amount): string
    {
        return number_format((float) $amount, 0, ',', '.') . 'đ';
    }
}
