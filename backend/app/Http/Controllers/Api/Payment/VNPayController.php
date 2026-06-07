<?php
// app/Http/Controllers/Api/Payment/VNPayController.php
namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class VNPayController extends Controller
{
    private string $vnpUrl;
    private string $tmnCode;
    private string $hashSecret;
    private string $returnUrl;

    public function __construct()
    {
        $this->vnpUrl     = config('services.vnpay.url', env('VNPAY_URL'));
        $this->tmnCode    = config('services.vnpay.tmn_code', env('VNPAY_TMN_CODE'));
        $this->hashSecret = config('services.vnpay.hash_secret', env('VNPAY_HASH_SECRET'));
        $this->returnUrl  = config('services.vnpay.return_url', env('VNPAY_RETURN_URL'));
    }

    // ─── TẠO URL THANH TOÁN VNPAY ────────────────────────
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'unpaid')
            ->firstOrFail();

        $txnRef  = $order->order_code . '_' . time();
        $amount  = (int)($order->final_amount * 100); // VNPay tính theo đơn vị đồng x100

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $this->tmnCode,
            'vnp_Amount'     => $amount,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $request->ip(),
            'vnp_Locale'     => 'vn',
            'vnp_OrderInfo'  => "Thanh toan don hang {$order->order_code}",
            'vnp_OrderType'  => 'other',
            'vnp_ReturnUrl'  => $this->returnUrl,
            'vnp_TxnRef'     => $txnRef,
        ];

        ksort($inputData);
        $query  = http_build_query($inputData);
        $hmac   = hash_hmac('sha512', $query, $this->hashSecret);
        $payUrl = $this->vnpUrl . '?' . $query . '&vnp_SecureHash=' . $hmac;

        // Lưu txnRef để đối soát
        $order->update(['vnpay_txn_ref' => $txnRef]);

        return response()->json([
            'success'  => true,
            'data'     => ['payment_url' => $payUrl],
            'message'  => 'Tạo URL thanh toán VNPay thành công.',
        ]);
    }

    // ─── CALLBACK TỪ VNPAY ───────────────────────────────
    public function return(Request $request)
    {
        $inputData = $request->all();
        $secureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $query    = http_build_query($inputData);
        $myHash   = hash_hmac('sha512', $query, $this->hashSecret);

        if ($myHash !== $secureHash) {
            return response()->json(['success' => false, 'message' => 'Chữ ký không hợp lệ.'], 400);
        }

        $txnRef      = $inputData['vnp_TxnRef'];
        $responseCode = $inputData['vnp_ResponseCode'];
        $orderCode   = explode('_', $txnRef)[0];

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không tồn tại.'], 404);
        }

        if ($responseCode === '00') {
            // Thanh toán thành công
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
            ]);

            // Redirect về frontend với thông báo thành công
            return redirect(env('FRONTEND_URL') . '/payment/success?order=' . $orderCode);
        } else {
            // Thanh toán thất bại
            return redirect(env('FRONTEND_URL') . '/payment/failed?order=' . $orderCode . '&code=' . $responseCode);
        }
    }

    public function callback(Request $request)
    {
        // IPN URL - VNPay gọi ngầm để cập nhật trạng thái
        return $this->return($request);
    }
}
