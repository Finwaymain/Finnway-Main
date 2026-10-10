<?php

namespace App\Services;

use App\Helpers\RazorpayConfig;
use Illuminate\Support\Facades\Log;

class UpiQrService
{
    /**
     * Generate standard NPCI compliant UPI Intent URI for a user/driver.
     *
     * @param string|null $acNo User's 12-digit pocket number
     * @param string|null $userName User's display name
     * @return string UPI Intent URI
     */
    public static function generateUpiStringForUser(?string $acNo, ?string $userName = null): string
    {
        $cleanAcNo = trim((string) $acNo);
        if (empty($cleanAcNo)) {
            return '';
        }

        try {
            $config = RazorpayConfig::resolve();
            $vpa = !empty($config['merchant_vpa']) ? $config['merchant_vpa'] : 'fiinway@icici';
            $merchantName = !empty($config['merchant_name']) ? $config['merchant_name'] : 'Fiinway';
        } catch (\Throwable $e) {
            $vpa = env('UPI_MERCHANT_VPA', 'fiinway@icici');
            $merchantName = env('RAZORPAY_MERCHANT_NAME', 'Fiinway');
        }

        $displayName = $merchantName;
        if (!empty($userName)) {
            $displayName = $merchantName . ' - ' . trim($userName);
        }

        $encodedPn = urlencode($displayName);
        $encodedTn = urlencode('Fiinway Wallet ' . $cleanAcNo);

        // Standard NPCI UPI URI Format:
        // pa = Payee VPA
        // pn = Payee Name
        // tr = Transaction Reference (used to map back to User A's ac_no)
        // tn = Transaction Note
        // cu = Currency (INR)
        return "upi://pay?pa={$vpa}&pn={$encodedPn}&tr={$cleanAcNo}&tn={$encodedTn}&cu=INR";
    }

    /**
     * Universal extractor: Safely extract User's 12-digit ac_no from ANY scanned data.
     * Works with:
     * 1. Plain 12-digit ac_no (e.g. '708012345678')
     * 2. Full UPI URI (e.g. 'upi://pay?pa=...&tr=708012345678&tn=...')
     * 3. JSON payload (e.g. '{"ac_no":"708012345678"}')
     * 4. URLs containing ac_no
     *
     * @param mixed $input
     * @return string Extracted ac_no or original string if no match
     */
    public static function extractAcNoFromScannedString($input): string
    {
        if (empty($input)) {
            return '';
        }

        $str = trim((string) $input);

        // 1. Direct match: If it's already a clean 10-14 digit number, return as is
        if (preg_match('/^[0-9]{10,14}$/', $str)) {
            return $str;
        }

        // 2. JSON check
        if (str_starts_with($str, '{') && str_ends_with($str, '}')) {
            $decoded = json_decode($str, true);
            if (is_array($decoded) && !empty($decoded['ac_no'])) {
                return trim((string) $decoded['ac_no']);
            }
        }

        // 3. UPI Intent check: upi://pay?...
        if (stripos($str, 'upi://pay') !== false || stripos($str, 'pa=') !== false) {
            $parsedUrl = parse_url($str);
            $queryStr = $parsedUrl['query'] ?? '';
            if (empty($queryStr) && str_contains($str, '?')) {
                $parts = explode('?', $str, 2);
                $queryStr = $parts[1] ?? '';
            }

            if (!empty($queryStr)) {
                parse_str($queryStr, $params);
                // Check 'tr' (transaction reference) first
                if (!empty($params['tr'])) {
                    $tr = trim((string) $params['tr']);
                    if (preg_match('/[0-9]{10,14}/', $tr, $m)) {
                        return $m[0];
                    }
                    return $tr;
                }

                // Check 'tn' (transaction note)
                if (!empty($params['tn'])) {
                    if (preg_match('/(7080|7060)[0-9]{8}/', $params['tn'], $m)) {
                        return $m[0];
                    }
                    if (preg_match('/[0-9]{12}/', $params['tn'], $m)) {
                        return $m[0];
                    }
                }
            }
        }

        // 4. Regex fallback: Look for Fiinway pocket number pattern (7080XXXXXXXX or 7060XXXXXXXX)
        if (preg_match('/(7080|7060)[0-9]{8}/', $str, $matches)) {
            return $matches[0];
        }

        // 5. Fallback: Any 12-digit number sequence
        if (preg_match('/\b[0-9]{12}\b/', $str, $matches)) {
            return $matches[0];
        }

        return $str;
    }
}
