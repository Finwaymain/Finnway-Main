<?php

namespace App\Http\Controllers\API\v1\payments;

use App\Helpers\RazorpayConfig;
use App\Http\Controllers\Controller;
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

        // We process payment.captured, payment.authorized, qr_code.credited, virtual_account.credited
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
        $rawAmount = $paymentEntity['amount'] ?? ($qrEntity['payment_amount'] ?? 0);
        $amountInRupees = round(floatval($rawAmount) / 100.0, 2);

        if ($amountInRupees <= 0) {
            return response()->json(['status' => 'ignored', 'message' => 'Zero amount'], 200);
        }

        // Idempotency guard: prevent duplicate crediting
        if (Schema::hasTable('upi_qr_transactions')) {
            $alreadyProcessed = DB::table('upi_qr_transactions')
                ->where('razorpay_payment_id', $paymentId)
                ->where('wallet_credited', true)
                ->exists();

            if ($alreadyProcessed) {
                Log::info('Razorpay Webhook: Payment ID ' . $paymentId . ' already processed.');
                return response()->json(['status' => 'success', 'message' => 'Already processed'], 200);
            }
        }

        // Payer information (User B)
        $payerVpa   = $paymentEntity['vpa'] ?? '';
        $payerPhone = $paymentEntity['contact'] ?? '';
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

        // Target Receiver (User A) ac_no identification
        $acNo = null;

        // 1. Check payment notes (including 'comment' from razorpay.me payment handles)
        if (!empty($paymentEntity['notes'])) {
            $notes = $paymentEntity['notes'];
            $acNo = $notes['comment'] ?? $notes['ac_no'] ?? $notes['account_no'] ?? $notes['user_ac_no'] ?? null;
        }

        // 2. Check payment_link notes if this was paid via a payment link
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

        // 4. Check payment entity top-level fields (tn, transaction_note, description, etc.)
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

        if (empty($acNo)) {
            Log::warning('Razorpay Webhook: Could not resolve recipient ac_no for payment ' . $paymentId, [
                'payment' => $paymentEntity,
                'raw_payload' => $rawPayload,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Recipient ac_no not found in notes'], 200);
        }

        $acNo = UpiQrService::extractAcNoFromScannedString($acNo);

        // Resolve User A in database (either customer or driver)
        $user = null;
        $userType = 'customer';

        $common = DB::table('common_user_base')->where('ac_no', $acNo)->first();
        if ($common) {
            $userType = ($common->user_type === 'driver') ? 'driver' : 'customer';
            $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
            $user = DB::table($table)->where('id', $common->user_id)->first();
        }

        if (!$user) {
            $user = DB::table('tj_user_app')->where('ac_no', $acNo)->orWhere('phone', $acNo)->first();
            if ($user) {
                $userType = 'customer';
            } else {
                $user = DB::table('tj_conducteur')->where('ac_no', $acNo)->orWhere('phone', $acNo)->first();
                if ($user) {
                    $userType = 'driver';
                }
            }
        }

        if (!$user) {
            Log::error('Razorpay Webhook: User not found for ac_no ' . $acNo);
            return response()->json(['status' => 'error', 'message' => 'User not found'], 200);
        }

        $userId = $user->id;
        $nowDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
        $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d');

        // Execute atomic wallet crediting
        DB::beginTransaction();
        try {
            // 1. Increment wallet balance
            if ($userType === 'driver') {
                DB::table('tj_conducteur')->where('id', $userId)->increment('amount', $amountInRupees);
            } else {
                DB::table('tj_user_app')->where('id', $userId)->increment('amount', $amountInRupees);
            }

            // 2. Insert into wallet transactions
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

            // 3. Record in upi_qr_transactions if table exists
            if (Schema::hasTable('upi_qr_transactions')) {
                DB::table('upi_qr_transactions')->insert([
                    'razorpay_payment_id' => $paymentId,
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
                ]);
            }

            DB::commit();
            Log::info("Razorpay Webhook: Successfully credited ₹{$amountInRupees} to {$userType} ID {$userId} ({$acNo})");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Razorpay Webhook DB Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Database error'], 500);
        }

        // Send Push Notification asynchronously / best-effort
        $this->notifyUserOfCredit($user, $userType, $amountInRupees, $payerName);

        return response()->json([
            'status'  => 'success',
            'message' => 'Wallet credited successfully',
            'user_id' => $userId,
            'amount'  => $amountInRupees,
        ], 200);
    }

    /**
     * Send real-time FCM notification to User A.
     */
    private function notifyUserOfCredit($user, string $userType, float $amount, string $payerName): void
    {
        try {
            $fcmId = $user->fcm_id ?? null;
            if (empty($fcmId)) {
                return;
            }

            $title = 'Money Received in Wallet';
            $body = '₹' . number_format($amount, 2) . ' received from ' . $payerName . ' via UPI!';

            // Check if Firebase service helper exists
            if (class_exists(\App\Services\FirebaseNotificationService::class)) {
                \App\Services\FirebaseNotificationService::sendNotification($fcmId, $title, $body, [
                    'type'   => 'wallet_credit',
                    'amount' => (string) $amount,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not send wallet credit push notification: ' . $e->getMessage());
        }
    }
}
