<?php

namespace App\Http\Controllers\API\v1\payments;

use App\Helpers\RazorpayConfig;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class RazorPayController extends Controller
{
    public function createOrderid(Request $request)
    {
        $input = $request->all();
        $rawAmount = $input['amount'] ?? 0;
        $amount = intval(round(floatval($rawAmount)));
        if ($amount < 1000) {
            return response()->json(['success' => 'Failed', 'error' => 'Minimum top-up amount is ₹10.'], 400);
        }
        if ($amount > 5000000) {
            return response()->json(['success' => 'Failed', 'error' => 'Maximum top-up amount per transaction is ₹50,000.'], 400);
        }

        $receipt_id = $input['receipt_id'] ?? ('rcpt_' . time() . '_' . rand(1000, 9999));
        $currency = $input['currency'] ?? 'INR';

        $config = RazorpayConfig::resolve();
        $razorpaykey = (!empty($input['razorpaykey'])) ? $input['razorpaykey'] : ($config['key'] ?? '');
        $razorPaySecret = (!empty($input['razorPaySecret'])) ? $input['razorPaySecret'] : ($config['secret'] ?? '');

        if (empty($razorpaykey) || empty($razorPaySecret)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Razorpay API keys are not configured. Please contact admin.',
                'message' => 'Razorpay API keys are not configured.',
            ], 400);
        }

        try {
            $client = new Api($razorpaykey, $razorPaySecret);
            $order = $client->order->create([
                'receipt'  => (string)$receipt_id,
                'amount'   => $amount,
                'currency' => $currency,
            ]);

            $attributes = $this->getProtectedValue($order, 'attributes');
            return response()->json($attributes);
        } catch (\Exception $e) {
            \Log::error('Razorpay createOrderid error: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => $e->getMessage(),
                'message' => $e->getMessage(),
                'faild' => $e->getMessage(),
            ], 400);
        }
    }

    public function getProtectedValue($obj, $name)
    {
        $array = (array)$obj;
        $prefix = chr(0) . '*' . chr(0);
        return $array[$prefix . $name] ?? null;
    }
}

