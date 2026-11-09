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
            $vpa = !empty($config['merchant_vpa']) ? $config['merchant_vpa'] : 'fiinway782717.rzp@rxairtel';
            $merchantName = !empty($config['merchant_name']) ? $config['merchant_name'] : 'Fiinway';
        } catch (\Throwable $e) {
            $vpa = env('UPI_MERCHANT_VPA', 'fiinway782717.rzp@rxairtel');
            $merchantName = env('RAZORPAY_MERCHANT_NAME', 'Fiinway');
        }

        $displayName = $merchantName;
        if (!empty($userName)) {
            $displayName = $merchantName . ' - ' . trim($userName);
        }

        $encodedPn = rawurlencode($displayName);
        $encodedTn = rawurlencode('Fiinway Wallet ' . $cleanAcNo);

        $mc = !empty($config['merchant_mc']) ? $config['merchant_mc'] : '5817';

        // Verified NPCI UPI URI Format with Registered Razorpay Standee Terminal ID:
        // pa = Payee VPA (fiinway782717.rzp@rxairtel)
        // pn = Payee Name
        // mc = Merchant Category Code (5817)
        // mode = UPI transfer mode (19 for Razorpay merchant standee)
        // tr = Active Razorpay Standee Terminal ID (Tm8NeVRNVXdRRaqrv2)
        // tn = Transaction Note with User A's clean pocket number
        // cu = Currency (INR)
        return "upi://pay?cu=INR&mc={$mc}&mode=19&pa={$vpa}&pn={$encodedPn}&tr=Tm8NeVRNVXdRRaqrv2&tn={$encodedTn}";
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
                // 1. Strictly check 'tr' and 'tn' for Fiinway pocket number format (7080XXXXXXXX or 7060XXXXXXXX) first
                foreach (['tr', 'tn'] as $paramKey) {
                    if (!empty($params[$paramKey])) {
                        if (preg_match('/(7080|7060)[0-9]{8}/', (string) $params[$paramKey], $m)) {
                            return $m[0];
                        }
                    }
                }

                // 2. Check 'tn' (transaction note) for any 12-digit pocket number
                if (!empty($params['tn']) && preg_match('/[0-9]{12}/', (string) $params['tn'], $m)) {
                    return $m[0];
                }

                // 3. Check 'tr' (transaction reference) for a 10-14 digit account number
                if (!empty($params['tr'])) {
                    $tr = trim((string) $params['tr']);
                    if (preg_match('/^[0-9]{10,14}$/', $tr, $m)) {
                        return $m[0];
                    }
                    // If 'tr' is a dedicated Razorpay QR reference (not the shared Tm8NeVRNVXdRRaqrv2 standee),
                    // check if any user/driver owns this dedicated QR string in the database
                    if ($tr !== 'Tm8NeVRNVXdRRaqrv2') {
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_user_app', 'razorpay_upi_string')) {
                                $u = \Illuminate\Support\Facades\DB::table('tj_user_app')
                                    ->where('razorpay_upi_string', 'LIKE', '%' . $tr . '%')
                                    ->whereNotNull('ac_no')
                                    ->first();
                                if ($u && !empty($u->ac_no)) {
                                    return trim((string) $u->ac_no);
                                }
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'razorpay_upi_string')) {
                                $d = \Illuminate\Support\Facades\DB::table('tj_conducteur')
                                    ->where('razorpay_upi_string', 'LIKE', '%' . $tr . '%')
                                    ->whereNotNull('ac_no')
                                    ->first();
                                if ($d && !empty($d->ac_no)) {
                                    return trim((string) $d->ac_no);
                                }
                            }
                        } catch (\Throwable $e) {
                            // Ignore DB lookup errors during static extraction
                        }
                    }
                }

                if (!empty($params['tr'])) {
                    return trim((string) $params['tr']);
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

    /**
     * Ensure razorpay_qr_id, razorpay_qr_image_url, and razorpay_upi_string columns exist on user/driver tables.
     */
    public static function ensureQrColumnsExist(string $table = 'tj_user_app'): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                return;
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'razorpay_qr_id')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` ADD COLUMN `razorpay_qr_id` VARCHAR(100) NULL DEFAULT NULL, ADD INDEX `idx_{$table}_rzp_qr_id` (`razorpay_qr_id`)");
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'razorpay_qr_image_url')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` ADD COLUMN `razorpay_qr_image_url` TEXT NULL DEFAULT NULL");
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'razorpay_upi_string')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` ADD COLUMN `razorpay_upi_string` TEXT NULL DEFAULT NULL");
            }
        } catch (\Throwable $e) {
            Log::warning("ensureQrColumnsExist({$table}): " . $e->getMessage());
        }
    }

    /**
     * Get or create a dedicated multiple_use Razorpay QR code for a user/driver.
     * Stored in user's profile and database for 100% bank-stripping immunity.
     *
     * @param int $userId
     * @param string $userType 'customer' or 'driver'
     * @param string $acNo 12-digit pocket account number
     * @param string|null $userName
     * @return array ['qr_id' => string, 'image_url' => string, 'upi_string' => string]
     */
    public static function getOrCreateRazorpayQrForUser(int $userId, string $userType, string $acNo, ?string $userName = null): array
    {
        $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        self::ensureQrColumnsExist($table);
        $user = \Illuminate\Support\Facades\DB::table($table)->where('id', $userId)->first();

        // 1. Check if user already has an active Razorpay QR stored
        if ($user && !empty($user->razorpay_qr_id) && !empty($user->razorpay_qr_image_url)) {
            $storedUpi = !empty($user->razorpay_upi_string) ? $user->razorpay_upi_string : self::generateUpiStringForUser($acNo, $userName);
            return [
                'qr_id'      => $user->razorpay_qr_id,
                'image_url'  => $user->razorpay_qr_image_url,
                'upi_string' => $storedUpi,
            ];
        }

        // 2. Call Razorpay API to mint a dedicated multiple_use QR code
        try {
            $config = RazorpayConfig::resolve();
            $key = $config['key'] ?? '';
            $secret = $config['secret'] ?? '';

            if (empty($key) || empty($secret)) {
                return [
                    'qr_id'      => null,
                    'image_url'  => null,
                    'upi_string' => self::generateUpiStringForUser($acNo, $userName),
                ];
            }

            $displayName = !empty($userName) ? trim($userName) : "User {$userId}";
            $payload = [
                'type'         => 'upi_qr',
                'name'         => substr($displayName, 0, 40),
                'usage'        => 'multiple_use',
                'fixed_amount' => false,
                'description'  => "Fiinway Wallet {$acNo}",
                'notes'        => [
                    'ac_no'     => (string) $acNo,
                    'user_id'   => (string) $userId,
                    'user_type' => (string) $userType,
                ],
            ];

            $ch = curl_init('https://api.razorpay.com/v1/payments/qr_codes');
            curl_setopt($ch, CURLOPT_USERPWD, "{$key}:{$secret}");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $res = json_decode($rawResponse, true);
                if (!empty($res['id']) && !empty($res['image_url'])) {
                    $qrId = $res['id'];
                    $imageUrl = $res['image_url'];
                    if (!empty($res['image_content']) && stripos((string) $res['image_content'], 'upi://pay') !== false) {
                        $upiString = trim((string) $res['image_content']);
                        if (stripos($upiString, 'tn=') === false && !empty($acNo)) {
                            $upiString .= (str_contains($upiString, '?') ? '&' : '?') . 'tn=' . rawurlencode('Fiinway Wallet ' . trim((string) $acNo));
                        }
                    } else {
                        $upiString = self::generateUpiStringForUser($acNo, $userName);
                    }

                    // Ensure columns exist or store gracefully
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'razorpay_qr_id')) {
                            \Illuminate\Support\Facades\DB::table($table)->where('id', $userId)->update([
                                'razorpay_qr_id'        => $qrId,
                                'razorpay_qr_image_url'  => $imageUrl,
                                'razorpay_upi_string'   => $upiString,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Could not persist razorpay_qr_id to {$table}: " . $e->getMessage());
                    }

                    return [
                        'qr_id'      => $qrId,
                        'image_url'  => $imageUrl,
                        'upi_string' => $upiString,
                    ];
                }
            } else {
                Log::warning("Razorpay QR creation HTTP {$httpCode}: {$rawResponse}");
            }
        } catch (\Throwable $e) {
            Log::error("getOrCreateRazorpayQrForUser exception: " . $e->getMessage());
        }

        // Fallback to standard UPI intent string
        return [
            'qr_id'      => null,
            'image_url'  => null,
            'upi_string' => self::generateUpiStringForUser($acNo, $userName),
        ];
    }
}
