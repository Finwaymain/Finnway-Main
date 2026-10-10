<?php

namespace App\Http\Controllers\API\v1\payments;

use App\Helpers\RazorpayConfig;
use App\Http\Controllers\Controller;
use App\Services\PhoneService;
use App\Services\UpiQrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RazorpayWebhookController extends Controller
{
    /**
     * Handle incoming Razorpay Webhook for UPI QR payments.
     */
    public function handleWebhook(Request $request)
    {
        $rawPayload = $request->getContent();
        $receivedSignature = $request->header('X-Razorpay-Signature');

        Log::info('Razorpay QR Webhook received', [
            'has_signature' => !empty($receivedSignature),
            'ip'            => $request->ip(),
        ]);

        $config = RazorpayConfig::resolve();
        $webhookSecret = $config['webhook_secret'];

        // Verify signature if secret is configured
        if (!empty($webhookSecret) && !empty($receivedSignature)) {
            $expectedSignature = hash_hmac('sha256', $rawPayload, $webhookSecret);
            if (!hash_equals($expectedSignature, $receivedSignature)) {
                Log::warning('Razorpay Webhook: Invalid HMAC Signature');
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
            }
        }

        $event = json_decode($rawPayload, true);
        if (!is_array($event) || empty($event['event'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid JSON payload'], 400);
        }

        $eventType = $event['event'];
        Log::info('Razorpay Webhook Event: ' . $eventType);

        // Self-healing schema: ensure user_id and ac_no are nullable in upi_qr_transactions
        $this->ensureSchemaNullable();

        // Extract entities from payload
        $payloadData = $event['payload'] ?? [];
        $paymentEntity = $payloadData['payment']['entity'] ?? null;
        $qrEntity = $payloadData['qr_code']['entity'] ?? null;

        if (!$paymentEntity && !$qrEntity) {
            return response()->json(['status' => 'ignored', 'message' => 'No payment or QR entity'], 200);
        }

        $paymentId = $paymentEntity['id'] ?? ($qrEntity['id'] ?? null);
        if (empty($paymentId)) {
            return response()->json(['status' => 'ignored', 'message' => 'Missing transaction ID'], 200);
        }

        // Amount in Razorpay is sent in paise (e.g. 50000 = ₹500.00)
        $rawAmount = $paymentEntity['amount'] ?? ($qrEntity['payment_amount'] ?? ($qrEntity['amount'] ?? 0));
        $amountInRupees = round(floatval($rawAmount) / 100.0, 2);

        if ($amountInRupees <= 0) {
            return response()->json(['status' => 'ignored', 'message' => 'Zero amount'], 200);
        }

        $nowDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
        $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d');

        // Payer information
        $payerVpa   = $paymentEntity['vpa'] ?? '';
        $payerPhone = $paymentEntity['contact'] ?? '';
        $payerEmail = $paymentEntity['email'] ?? '';
        $payerName  = $paymentEntity['notes']['payer_name'] ?? ($paymentEntity['acquirer_data']['bank_transaction_id'] ?? null);

        // Derive friendly Payer Name if not explicitly provided
        if (empty($payerName)) {
            if (!empty($payerVpa)) {
                $parts = explode('@', $payerVpa);
                $payerName = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
            } elseif (!empty($payerPhone)) {
                $payerName = 'UPI User (' . substr($payerPhone, -4) . ')';
            } else {
                $payerName = 'UPI User';
            }
        }

        // Handle payment failure event
        if ($eventType === 'payment.failed') {
            if (Schema::hasTable('upi_qr_transactions')) {
                DB::table('upi_qr_transactions')->updateOrInsert(
                    ['razorpay_payment_id' => $paymentId],
                    [
                        'amount'          => $amountInRupees,
                        'payer_name'      => $payerName,
                        'payer_vpa'       => $payerVpa,
                        'payer_phone'     => $payerPhone,
                        'status'          => 'failed',
                        'wallet_credited' => false,
                        'raw_payload'     => $rawPayload,
                        'updated_at'      => $nowDateTime,
                    ]
                );
            }
            return response()->json(['status' => 'success', 'message' => 'Recorded failed payment'], 200);
        }

        // Idempotency guard: prevent duplicate crediting if already credited
        if (Schema::hasTable('upi_qr_transactions')) {
            $existing = DB::table('upi_qr_transactions')
                ->where('razorpay_payment_id', $paymentId)
                ->first();

            if ($existing && $existing->wallet_credited) {
                Log::info('Razorpay Webhook: Payment ID ' . $paymentId . ' already credited.');
                return response()->json(['status' => 'success', 'message' => 'Already processed and credited'], 200);
            }
        }

        // ── Resolve Recipient User & Account ────────────────────────────────────
        $resolved = $this->resolveRecipient($paymentEntity, $qrEntity, $payloadData, $rawPayload, $payerPhone, $payerVpa, $payerEmail);
        $user     = $resolved['user'];
        $userType = $resolved['user_type'];
        $acNo     = $resolved['ac_no'];

        // ── If User Found: Execute Credit & Record ──────────────────────────────
        if ($user) {
            $userId = $user->id;
            DB::beginTransaction();
            try {
                // 1. Increment wallet balance
                if ($userType === 'driver') {
                    DB::table('tj_conducteur')->where('id', $userId)->increment('amount', $amountInRupees);
                } else {
                    DB::table('tj_user_app')->where('id', $userId)->increment('amount', $amountInRupees);
                }

                // 2. Insert wallet transaction record
                $txnData = [
                    'amount'          => (string) $amountInRupees,
                    'type'            => 'credit',
                    'deduction_type'  => 1, // 1 = Credit
                    'payment_method'  => 'UPI',
                    'counterparty'    => $payerName,
                    'payment_status'  => 'success',
                    'txn_id'          => $paymentId,
                    'description'     => 'UPI Payment from ' . $payerName . ($payerVpa ? ' (' . $payerVpa . ')' : ''),
                    'date'            => $currentDate,
                    'creer'           => $nowDateTime,
                    'modifier'        => $nowDateTime,
                ];

                if ($userType === 'driver') {
                    $txnData['id_conducteur'] = $userId;
                    DB::table('tj_conducteur_transaction')->insert($txnData);
                } else {
                    $txnData['id_user_app'] = $userId;
                    $txnData['ac_no']       = $user->ac_no ?? $acNo;
                    DB::table('tj_transaction')->insert($txnData);
                }

                // 3. Upsert into upi_qr_transactions
                if (Schema::hasTable('upi_qr_transactions')) {
                    DB::table('upi_qr_transactions')->updateOrInsert(
                        ['razorpay_payment_id' => $paymentId],
                        [
                            'razorpay_order_id'   => $paymentEntity['order_id'] ?? null,
                            'ac_no'               => $user->ac_no ?? $acNo,
                            'user_id'             => $userId,
                            'user_type'           => $userType,
                            'amount'              => $amountInRupees,
                            'fee'                 => round(floatval($paymentEntity['fee'] ?? 0) / 100.0, 2),
                            'tax'                 => round(floatval($paymentEntity['tax'] ?? 0) / 100.0, 2),
                            'payer_name'          => $payerName,
                            'payer_vpa'           => $payerVpa,
                            'payer_phone'         => $payerPhone,
                            'payment_method'      => 'UPI',
                            'status'              => 'captured',
                            'wallet_credited'     => true,
                            'raw_payload'         => $rawPayload,
                            'created_at'          => $nowDateTime,
                            'updated_at'          => $nowDateTime,
                        ]
                    );
                }

                DB::commit();
                Log::info("Razorpay Webhook: Successfully credited ₹{$amountInRupees} to {$userType} ID {$userId} ({$acNo})");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Razorpay Webhook DB Error: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Database error'], 500);
            }

            // Send real-time FCM push notification
            $this->notifyUserOfCredit($user, $userType, $amountInRupees, $payerName);

            return response()->json([
                'status'  => 'success',
                'message' => 'Wallet credited successfully',
                'user_id' => $userId,
                'amount'  => $amountInRupees,
            ], 200);
        }

        // ── If User Not Yet Identified: Record as Unassigned ─────────────────────
        // Transaction is safely saved so Admin sees it in the table and can manually assign/credit it
        Log::warning('Razorpay Webhook: Recipient could not be auto-resolved for payment ' . $paymentId . '. Recording as unassigned.', [
            'payment' => $paymentEntity,
            'payerPhone' => $payerPhone,
            'payerVpa' => $payerVpa,
        ]);

        if (Schema::hasTable('upi_qr_transactions')) {
            DB::table('upi_qr_transactions')->updateOrInsert(
                ['razorpay_payment_id' => $paymentId],
                [
                    'razorpay_order_id'   => $paymentEntity['order_id'] ?? null,
                    'ac_no'               => $acNo,
                    'user_id'             => null,
                    'user_type'           => 'customer',
                    'amount'              => $amountInRupees,
                    'fee'                 => round(floatval($paymentEntity['fee'] ?? 0) / 100.0, 2),
                    'tax'                 => round(floatval($paymentEntity['tax'] ?? 0) / 100.0, 2),
                    'payer_name'          => $payerName,
                    'payer_vpa'           => $payerVpa,
                    'payer_phone'         => $payerPhone,
                    'payment_method'      => 'UPI',
                    'status'              => 'unassigned',
                    'wallet_credited'     => false,
                    'raw_payload'         => $rawPayload,
                    'created_at'          => $nowDateTime,
                    'updated_at'          => $nowDateTime,
                ]
            );
        }

        return response()->json([
            'status'  => 'recorded_unassigned',
            'message' => 'Payment recorded in admin panel pending user assignment',
            'payment_id' => $paymentId,
            'amount'  => $amountInRupees,
        ], 200);
    }

    /**
     * Resolve recipient user through multi-level waterfall strategy.
     */
    private function resolveRecipient($paymentEntity, $qrEntity, array $payloadData, string $rawPayload, ?string $payerPhone, ?string $payerVpa, ?string $payerEmail): array
    {
        $acNo = null;

        // 1. Check payment entity notes
        if (!empty($paymentEntity['notes'])) {
            $notes = $paymentEntity['notes'];
            $acNo = $notes['comment'] ?? $notes['ac_no'] ?? $notes['account_no'] ?? $notes['user_ac_no'] ?? $notes['recipient_ac_no'] ?? null;
            if (empty($acNo)) {
                foreach ($notes as $val) {
                    $candidate = UpiQrService::extractAcNoFromScannedString((string)$val);
                    if (!empty($candidate) && preg_match('/^[0-9]{10,14}$/', $candidate)) {
                        $acNo = $candidate;
                        break;
                    }
                }
            }
        }

        // 2. Check payment_link notes
        $paymentLinkEntity = $payloadData['payment_link']['entity'] ?? null;
        if (empty($acNo) && !empty($paymentLinkEntity['notes'])) {
            $plNotes = $paymentLinkEntity['notes'];
            $acNo = $plNotes['comment'] ?? $plNotes['ac_no'] ?? $plNotes['account_no'] ?? null;
        }

        // 3. Check QR notes
        if (empty($acNo) && !empty($qrEntity['notes'])) {
            $qrNotes = $qrEntity['notes'];
            $acNo = $qrNotes['ac_no'] ?? $qrNotes['account_no'] ?? null;
        }

        // 4. Check payment entity top-level fields (tn, transaction_note, etc.)
        if (empty($acNo)) {
            $tnCandidate = $paymentEntity['tn'] 
                ?? $paymentEntity['transaction_note'] 
                ?? ($paymentEntity['acquirer_data']['tn'] ?? null) 
                ?? ($paymentEntity['acquirer_data']['transaction_note'] ?? null);
            if (!empty($tnCandidate)) {
                $extracted = UpiQrService::extractAcNoFromScannedString($tnCandidate);
                if (!empty($extracted) && preg_match('/^[0-9]{10,14}$/', $extracted)) {
                    $acNo = $extracted;
                }
            }
        }

        // 5. Check description / reference for pocket number
        if (empty($acNo)) {
            $desc = ($paymentEntity['description'] ?? '') . ' ' . ($paymentEntity['order_id'] ?? '');
            $extracted = UpiQrService::extractAcNoFromScannedString($desc);
            if (!empty($extracted) && preg_match('/^[0-9]{10,14}$/', $extracted)) {
                $acNo = $extracted;
            }
        }

        // 6. Deep scan entire raw payload for pocket account number (7080XXXXXXXX or 7060XXXXXXXX)
        if (empty($acNo)) {
            $extracted = UpiQrService::extractAcNoFromScannedString($rawPayload);
            if (!empty($extracted) && preg_match('/^[0-9]{10,14}$/', $extracted)) {
                $acNo = $extracted;
            }
        }

        // Clean extracted acNo if any
        if (!empty($acNo)) {
            $acNo = UpiQrService::extractAcNoFromScannedString($acNo);
        }

        // Try looking up user by ac_no
        $user = null;
        $userType = 'customer';

        if (!empty($acNo)) {
            $common = DB::table('common_user_base')->where('ac_no', $acNo)->first();
            if ($common) {
                $userType = ($common->user_type === 'driver') ? 'driver' : 'customer';
                $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
                $user = DB::table($table)->where('id', $common->user_id)->first();
            }

            if (!$user) {
                $user = DB::table('tj_user_app')->where('ac_no', $acNo)->first();
                if ($user) {
                    $userType = 'customer';
                } else {
                    $user = DB::table('tj_conducteur')->where('ac_no', $acNo)->first();
                    if ($user) {
                        $userType = 'driver';
                    }
                }
            }
        }

        // 7. Fallback: Lookup by Payer Contact Phone Number
        if (!$user && !empty($payerPhone)) {
            $phoneVariants = PhoneService::getVariants($payerPhone);
            $digits10 = PhoneService::getLast10($payerPhone);
            if (!empty($digits10)) {
                $phoneVariants[] = $digits10;
                $phoneVariants[] = '+91' . $digits10;
                $phoneVariants[] = '91' . $digits10;
                $phoneVariants[] = '0' . $digits10;
            }
            $phoneVariants = array_values(array_unique(array_filter($phoneVariants)));

            $user = DB::table('tj_user_app')->whereIn('phone', $phoneVariants)->first();
            if ($user) {
                $userType = 'customer';
                $acNo = $user->ac_no ?? $acNo;
            } else {
                $user = DB::table('tj_conducteur')->whereIn('phone', $phoneVariants)->first();
                if ($user) {
                    $userType = 'driver';
                    $acNo = $user->ac_no ?? $acNo;
                }
            }
        }

        // 8. Fallback: Lookup by 10-digit mobile embedded in Payer VPA (e.g. 9876543210@ybl)
        if (!$user && !empty($payerVpa)) {
            if (preg_match('/^([6-9]\d{9})@/i', $payerVpa, $vpaMatch)) {
                $vpaPhone = $vpaMatch[1];
                $vpaVariants = PhoneService::getVariants($vpaPhone);
                $vpaVariants[] = $vpaPhone;
                $vpaVariants[] = '+91' . $vpaPhone;
                $vpaVariants = array_values(array_unique(array_filter($vpaVariants)));

                $user = DB::table('tj_user_app')->whereIn('phone', $vpaVariants)->first();
                if ($user) {
                    $userType = 'customer';
                    $acNo = $user->ac_no ?? $acNo;
                } else {
                    $user = DB::table('tj_conducteur')->whereIn('phone', $vpaVariants)->first();
                    if ($user) {
                        $userType = 'driver';
                        $acNo = $user->ac_no ?? $acNo;
                    }
                }
            }
        }

        // 9. Fallback: Lookup by Payer Email
        if (!$user && !empty($payerEmail)) {
            $user = DB::table('tj_user_app')->where('email', $payerEmail)->first();
            if ($user) {
                $userType = 'customer';
                $acNo = $user->ac_no ?? $acNo;
            } else {
                $user = DB::table('tj_conducteur')->where('email', $payerEmail)->first();
                if ($user) {
                    $userType = 'driver';
                    $acNo = $user->ac_no ?? $acNo;
                }
            }
        }

        return [
            'user'      => $user,
            'user_type' => $userType,
            'ac_no'     => $acNo ?? ($user->ac_no ?? null),
        ];
    }

    /**
     * Ensure user_id and ac_no are nullable in database.
     */
    private function ensureSchemaNullable(): void
    {
        if (Schema::hasTable('upi_qr_transactions')) {
            try {
                DB::statement("ALTER TABLE `upi_qr_transactions` MODIFY `user_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `upi_qr_transactions` MODIFY `ac_no` VARCHAR(50) NULL");
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Send real-time FCM notification to User A and persist in tj_notification.
     */
    private function notifyUserOfCredit($user, string $userType, float $amount, string $payerName): void
    {
        try {
            $fcmId = $user->fcm_id ?? null;
            $userId = $user->id ?? null;
            $nowDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');

            $title = 'Fiinway';
            $formattedAmount = number_format($amount, 2);
            $messageBody = "Your Fiinway account has been credited with ₹{$formattedAmount} from {$payerName} via UPI.";

            // 1. Send FCM push notification via GcmController
            if (!empty($fcmId)) {
                $notifPayload = [
                    'title'     => $title,
                    'body'      => $messageBody,
                    'sound'     => 'default',
                    'tag'       => 'wallet_topup',
                    'type'      => 'wallet',
                    'amount'    => (string) $amount,
                    'user_type' => $userType,
                ];

                \App\Http\Controllers\API\v1\GcmController::sendNotification($fcmId, $notifPayload);
                Log::info("Razorpay Webhook: Sent FCM push notification to {$userType} ID {$userId} (fcm: " . substr($fcmId, 0, 15) . "...)");
            } else {
                Log::warning("Razorpay Webhook: User {$userId} does not have an active fcm_id token.");
            }

            // 2. Persist in tj_notification table so it shows in app notification list
            if ($userId && Schema::hasTable('tj_notification')) {
                DB::table('tj_notification')->insert([
                    'titre'    => $title,
                    'message'  => $messageBody,
                    'statut'   => 'yes',
                    'creer'    => $nowDateTime,
                    'modifier' => $nowDateTime,
                    'to_id'    => $userId,
                    'from_id'  => 0,
                    'type'     => 'wallet_topup',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not send wallet credit notification: ' . $e->getMessage());
        }
    }
}
