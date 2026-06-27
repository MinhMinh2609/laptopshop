<?php

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
        $amount  = (int) ($order->final_amount * 100);

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

        $order->update(['vnpay_txn_ref' => $txnRef]);

        return response()->json([
            'success' => true,
            'data'    => ['payment_url' => $payUrl],
            'message' => 'Tạo URL thanh toán VNPay thành công.',
        ]);
    }

    public function return(Request $request)
    {
        $result = $this->handleVnpayResult($request);
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $orderCode = $result['order_code'] ?? '';

        if (!$result['success']) {
            return redirect($frontendUrl . '/payment/failed?order=' . $orderCode . '&code=' . $result['code']);
        }

        return redirect($frontendUrl . '/payment/success?order=' . $orderCode);
    }

    public function callback(Request $request)
    {
        $result = $this->handleVnpayResult($request);

        return response()->json([
            'RspCode' => $result['ipn_code'],
            'Message' => $result['message'],
        ]);
    }

    private function handleVnpayResult(Request $request): array
    {
        $inputData = $request->all();
        $secureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $query  = http_build_query($inputData);
        $myHash = hash_hmac('sha512', $query, $this->hashSecret);

        if (!hash_equals($myHash, $secureHash)) {
            return [
                'success' => false,
                'code' => '97',
                'ipn_code' => '97',
                'message' => 'Invalid signature',
            ];
        }

        $txnRef = $inputData['vnp_TxnRef'] ?? '';
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';
        $transactionStatus = $inputData['vnp_TransactionStatus'] ?? $responseCode;
        $orderCode = explode('_', $txnRef)[0] ?? '';

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return [
                'success' => false,
                'code' => '01',
                'ipn_code' => '01',
                'message' => 'Order not found',
                'order_code' => $orderCode,
            ];
        }

        if ($order->vnpay_txn_ref !== $txnRef) {
            return [
                'success' => false,
                'code' => '02',
                'ipn_code' => '02',
                'message' => 'TxnRef mismatch',
                'order_code' => $orderCode,
            ];
        }

        $paidAmount = (int) ($inputData['vnp_Amount'] ?? 0);
        $expectedAmount = (int) ($order->final_amount * 100);

        if ($paidAmount !== $expectedAmount) {
            return [
                'success' => false,
                'code' => '04',
                'ipn_code' => '04',
                'message' => 'Invalid amount',
                'order_code' => $orderCode,
            ];
        }

        if ($responseCode === '00' && $transactionStatus === '00') {
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);
            }

            return [
                'success' => true,
                'code' => '00',
                'ipn_code' => '00',
                'message' => 'Confirm Success',
                'order_code' => $orderCode,
            ];
        }

        return [
            'success' => false,
            'code' => $responseCode ?: '99',
            'ipn_code' => '00',
            'message' => 'Payment failed or cancelled',
            'order_code' => $orderCode,
        ];
    }
}
