<?php

namespace App\Http\Controllers;

use App\Helpers\RazorpayConfig;
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
            return response()->json(['failed' => 'Minimum top-up amount is ₹10.'], 400);
        }
        if ($amount > 5000000) {
            return response()->json(['failed' => 'Maximum top-up amount per transaction is ₹50,000.'], 400);
        }
        $receipt_id = $input['receipt_id'] ?? ('rcpt_' . time() . '_' . rand(1000, 9999));
        $currency = $input['currency'] ?? 'INR';

        $config = RazorpayConfig::resolve();
        $razorpaykey = $config['key'] ?: ($input['razorpaykey'] ?? '');
        $razorPaySecret = $config['secret'] ?: ($input['razorPaySecret'] ?? '');

        if (empty($razorpaykey) || empty($razorPaySecret)) {
            return response()->json(['failed' => 'Razorpay API keys are not configured in admin panel.'], 400);
        }

        if (!$config['is_enabled']) {
            return response()->json(['failed' => 'Razorpay payments are disabled in admin panel.'], 400);
        }

        $client = new Api($razorpaykey, $razorPaySecret);

        try {
            $order = $client->order->create([
                'receipt' => $receipt_id,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            $attributes = $this->getProtectedValue($order, 'attributes');
            return response()->json($attributes);
        } catch (\Exception $e) {
            return response()->json(['failed' => $e->getMessage()]);
        }
    }

    public function getProtectedValue($obj, $name)
    {
        $array = (array) $obj;
        $prefix = chr(0) . '*' . chr(0);

        return $array[$prefix . $name];
    }
}
