<?php

namespace App\Helpers;

use App\Models\ApiKeySetting;
use App\Models\PaymentSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RazorpayConfig
{
    /**
     * Resolve Razorpay credentials from admin panels.
     * Prefers active api_key_settings (Administration → API Keys),
     * falls back to payment_settings (Settings → Payment → Razorpay).
     */
    public static function resolve(): array
    {
        $paymentRow = DB::table('payment_settings')
            ->where('id_payment_method', 13)
            ->first();

        $key = $paymentRow->key ?? '';
        $secret = $paymentRow->secret_key ?? '';
        $isEnabled = ($paymentRow->isEnabled ?? 'false') === 'true';
        $isSandbox = ($paymentRow->isSandboxEnabled ?? 'false') === 'true';

        $webhookSecret = (string) env('RAZORPAY_WEBHOOK_SECRET', '');
        $merchantVpa   = (string) env('RAZORPAY_MERCHANT_VPA', '');
        $merchantName  = (string) env('RAZORPAY_MERCHANT_NAME', 'Fiinway');

        if (Schema::hasTable('api_key_settings')) {
            $apiKey = ApiKeySetting::where('provider', 'razorpay')->first();
            if ($apiKey) {
                if (!empty($apiKey->key_value)) {
                    $key = $apiKey->key_value;
                }
                if (!empty($apiKey->secret_value)) {
                    $secret = $apiKey->secret_value;
                }
                if ($apiKey->is_active) {
                    $isEnabled = true;
                }
                $isSandbox = (bool) $apiKey->is_sandbox;

                if (!empty($apiKey->additional_params)) {
                    $params = is_array($apiKey->additional_params) ? $apiKey->additional_params : json_decode($apiKey->additional_params, true);
                    if (is_array($params)) {
                        $webhookSecret = $params['webhook_secret'] ?? $webhookSecret;
                        $merchantVpa   = $params['merchant_vpa'] ?? $params['vpa'] ?? $merchantVpa;
                        $merchantName  = $params['merchant_name'] ?? $merchantName;
                    }
                }
            }

            // Check if dedicated key records exist
            $whKey = ApiKeySetting::where('provider', 'razorpay')->where('key_name', 'razorpay_webhook_secret')->first();
            if ($whKey && !empty($whKey->key_value)) {
                $webhookSecret = $whKey->key_value;
            }

            $vpaKey = ApiKeySetting::where('provider', 'razorpay')->where('key_name', 'razorpay_merchant_vpa')->first();
            if ($vpaKey && !empty($vpaKey->key_value)) {
                $merchantVpa = $vpaKey->key_value;
            }

            $nameKey = ApiKeySetting::where('provider', 'razorpay')->where('key_name', 'razorpay_merchant_name')->first();
            if ($nameKey && !empty($nameKey->key_value)) {
                $merchantName = $nameKey->key_value;
            }
        }

        // If merchant VPA is still empty, derive a default handle from key or merchant name
        if (empty($merchantVpa)) {
            $merchantVpa = env('UPI_MERCHANT_VPA', 'fiinway@icici');
        }

        return [
            'key' => trim((string) $key),
            'secret' => trim((string) $secret),
            'webhook_secret' => trim((string) $webhookSecret),
            'merchant_vpa' => trim((string) $merchantVpa),
            'merchant_name' => trim((string) $merchantName),
            'is_enabled' => $isEnabled,
            'is_sandbox' => $isSandbox,
            'payment_row' => $paymentRow,
        ];
    }

    public static function toApiPayload(): ?object
    {
        $config = self::resolve();
        if (empty($config['key']) && empty($config['secret'])) {
            return null;
        }

        $row = $config['payment_row'];

        return (object) [
            'id' => (string) ($row->id ?? '0'),
            'key' => $config['key'],
            'secret_key' => $config['secret'],
            'isEnabled' => $config['is_enabled'] ? 'true' : 'false',
            'isSandboxEnabled' => $config['is_sandbox'] ? 'true' : 'false',
            'id_payment_method' => (string) ($row->id_payment_method ?? '13'),
            'libelle' => 'Razorpay',
        ];
    }

    public static function syncToPaymentSettings(string $key, string $secret, bool $isEnabled, bool $isSandbox): void
    {
        $settings = PaymentSettings::where('id_payment_method', 13)->first();
        if (!$settings) {
            return;
        }

        $settings->key = $key;
        $settings->secret_key = $secret;
        $settings->isEnabled = $isEnabled ? 'true' : 'false';
        $settings->isSandboxEnabled = $isSandbox ? 'true' : 'false';
        $settings->modifier = date('Y-m-d H:i:s');
        $settings->save();
    }

    public static function syncToApiKeySettings(string $key, string $secret, bool $isEnabled, bool $isSandbox): void
    {
        if (!Schema::hasTable('api_key_settings')) {
            return;
        }

        ApiKeySetting::updateOrCreate(
            ['provider' => 'razorpay', 'key_name' => 'razorpay_key_id'],
            [
                'group' => 'payment',
                'key_value' => $key,
                'secret_value' => $secret,
                'is_active' => $isEnabled,
                'is_sandbox' => $isSandbox,
            ]
        );
    }
}
