<?php

namespace App\Helpers;

use App\Models\Finance\FinanceSetting;
use App\Models\ApiKeySetting;
use Illuminate\Support\Facades\Schema;

class LoanRazorpayConfig
{
    /**
     * Resolve Razorpay credentials specifically for the Loan & Credit Ecosystem.
     * Isolated from the main app / food / ride-hailing / payment_settings key 13.
     */
    public static function resolve(): array
    {
        $key = '';
        $secret = '';
        $isEnabled = true;
        $isSandbox = false;
        $webhookSecret = '';
        $merchantName = 'Fiinway Loan & Credit';

        // 1. Primary: finance_settings
        if (Schema::hasTable('finance_settings')) {
            $key = (string) FinanceSetting::get('loan_razorpay_key_id', '');
            $secret = (string) FinanceSetting::get('loan_razorpay_key_secret', '');
            $isEnabled = (bool) FinanceSetting::get('loan_razorpay_is_enabled', true);
            $isSandbox = (bool) FinanceSetting::get('loan_razorpay_is_sandbox', false);
            $webhookSecret = (string) FinanceSetting::get('loan_razorpay_webhook_secret', '');
            $merchantName = (string) FinanceSetting::get('loan_razorpay_merchant_name', 'Fiinway Loan & Credit');
        }

        // 2. Secondary fallback: api_key_settings with provider = 'loan_razorpay'
        if (empty($key) && Schema::hasTable('api_key_settings')) {
            $apiSetting = ApiKeySetting::where('provider', 'loan_razorpay')->first();
            if ($apiSetting) {
                $key = $apiSetting->key_value ?? '';
                $secret = $apiSetting->secret_value ?? '';
                $isEnabled = (bool) ($apiSetting->is_active ?? true);
                $isSandbox = (bool) ($apiSetting->is_sandbox ?? false);
            }
        }

        // 3. Fallback: Environment variable LOAN_RAZORPAY_KEY (if set)
        if (empty($key)) {
            $key = (string) env('LOAN_RAZORPAY_KEY', '');
            $secret = (string) env('LOAN_RAZORPAY_SECRET', '');
        }

        $key = trim((string) $key);
        $secret = trim((string) $secret);

        return [
            'key' => $key,
            'secret' => $secret,
            'is_enabled' => (bool) $isEnabled,
            'is_sandbox' => (bool) $isSandbox,
            'webhook_secret' => trim((string) $webhookSecret),
            'merchant_name' => trim((string) $merchantName) ?: 'Fiinway Loan & Credit',
            'is_configured' => !empty($key),
        ];
    }

    /**
     * Save Razorpay credentials specifically for the Loan Ecosystem.
     */
    public static function save(array $data): void
    {
        $key = trim((string) ($data['key'] ?? ''));
        $secret = trim((string) ($data['secret'] ?? ''));
        $isEnabled = !empty($data['is_enabled']) ? '1' : '0';
        $isSandbox = !empty($data['is_sandbox']) ? '1' : '0';
        $webhookSecret = trim((string) ($data['webhook_secret'] ?? ''));
        $merchantName = trim((string) ($data['merchant_name'] ?? 'Fiinway Loan & Credit')) ?: 'Fiinway Loan & Credit';

        if (Schema::hasTable('finance_settings')) {
            FinanceSetting::set('loan_razorpay_key_id', $key, 'payment', 'string', 'Razorpay Key ID for Loan Flow');
            FinanceSetting::set('loan_razorpay_key_secret', $secret, 'payment', 'string', 'Razorpay Key Secret for Loan Flow');
            FinanceSetting::set('loan_razorpay_is_enabled', $isEnabled, 'payment', 'boolean', 'Enable Razorpay for Loan Flow');
            FinanceSetting::set('loan_razorpay_is_sandbox', $isSandbox, 'payment', 'boolean', 'Test / Sandbox Mode for Loan Flow');
            FinanceSetting::set('loan_razorpay_webhook_secret', $webhookSecret, 'payment', 'string', 'Razorpay Webhook Secret for Loan Flow');
            FinanceSetting::set('loan_razorpay_merchant_name', $merchantName, 'payment', 'string', 'Display Merchant Name in Loan Checkout');
        }

        // Mirror in api_key_settings with provider = 'loan_razorpay' without touching main 'razorpay'
        if (Schema::hasTable('api_key_settings')) {
            ApiKeySetting::updateOrCreate(
                ['provider' => 'loan_razorpay', 'key_name' => 'loan_razorpay_key_id'],
                [
                    'group' => 'finance',
                    'key_value' => $key,
                    'secret_value' => $secret,
                    'is_active' => (bool) $isEnabled,
                    'is_sandbox' => (bool) $isSandbox,
                    'additional_params' => [
                        'merchant_name' => $merchantName,
                        'webhook_secret' => $webhookSecret,
                    ],
                ]
            );
        }
    }

    /**
     * Test Razorpay connectivity using API
     */
    public static function testConnection(?string $key = null, ?string $secret = null): array
    {
        $config = self::resolve();
        $testKey = $key ?: $config['key'];
        $testSecret = $secret ?: $config['secret'];

        if (empty($testKey) || empty($testSecret)) {
            return [
                'success' => false,
                'message' => 'Both Key ID and Key Secret are required to test connection.',
            ];
        }

        try {
            if (class_exists(\Razorpay\Api\Api::class)) {
                $api = new \Razorpay\Api\Api($testKey, $testSecret);
                // Call lightweight Razorpay endpoint: fetch orders with limit 1
                $orders = $api->order->all(['count' => 1]);
                return [
                    'success' => true,
                    'message' => 'Connection successful! Razorpay authenticated and responded.',
                ];
            }

            // HTTP fallback using cURL if class not available
            $ch = curl_init('https://api.razorpay.com/v1/orders?count=1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $testKey . ':' . $testSecret);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Razorpay authenticated and responded.',
                ];
            }

            $json = json_decode((string) $res, true);
            $errorDesc = $json['error']['description'] ?? 'Invalid Razorpay credentials (HTTP ' . $httpCode . ')';
            return [
                'success' => false,
                'message' => 'Razorpay Error: ' . $errorDesc,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
