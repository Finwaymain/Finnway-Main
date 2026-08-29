<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\UserApp;

class MedicalCashbackController extends Controller
{
    private function getAuthenticatedUserAndType(Request $request)
    {
        $token = trim((string)(
            $request->header('accesstoken')
            ?? $request->query('accesstoken')
            ?? $request->input('accesstoken')
            ?? $request->json('accesstoken')
            ?? $_SERVER['HTTP_ACCESSTOKEN'] ?? ''
        ));

        if ($token !== '') {
            $access = DB::table('users_access')->where('accesstoken', $token)->first();
            if ($access && !empty($access->user_id)) {
                $userType = ($access->user_type === 'driver') ? 'driver' : 'customer';
                return [
                    'user_id' => (string)$access->user_id,
                    'user_type' => $userType
                ];
            }
        }

        $driverId = $request->input('driver_id') ?? $request->query('driver_id') ?? $request->header('driver_id');
        if (!empty($driverId)) {
            return [
                'user_id' => (string)$driverId,
                'user_type' => 'driver'
            ];
        }

        $userId = $request->input('user_id') ?? $request->query('user_id') ?? $request->header('user_id');
        if (!empty($userId)) {
            $isDriver = DB::table('tj_conducteur')->where('id', $userId)->exists();
            return [
                'user_id' => (string)$userId,
                'user_type' => $isDriver ? 'driver' : 'customer'
            ];
        }

        return null;
    }

    private function recordWalletTransaction($userId, $userType, $amount, $type, $description, $txnId = null)
    {
        $date = now()->toDateTimeString();
        $txnId = $txnId ?? 'MC_' . time() . rand(100, 999);

        if ($userType === 'driver') {
            $driver = DB::table('tj_conducteur')->where('id', $userId)->first();
            $oldBalance = floatval($driver->amount ?? 0);
            $newBalance = ($type === 'debit') ? max(0, $oldBalance - $amount) : ($oldBalance + $amount);

            DB::table('tj_conducteur')->where('id', $userId)->update(['amount' => $newBalance]);

            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur' => $userId,
                'amount' => $amount,
                'deduction_type' => $type,
                'date' => $date,
                'description' => $description,
                'txn_id' => $txnId,
                'creer' => $date,
                'modifier' => $date,
            ]);
        } else {
            $user = DB::table('tj_user_app')->where('id', $userId)->first();
            $oldBalance = floatval($user->amount ?? 0);
            $newBalance = ($type === 'debit') ? max(0, $oldBalance - $amount) : ($oldBalance + $amount);

            DB::table('tj_user_app')->where('id', $userId)->update(['amount' => $newBalance]);

            DB::table('tj_transaction')->insert([
                'id_user_app' => $userId,
                'amount' => $amount,
                'deduction_type' => $type,
                'date' => $date,
                'description' => $description,
                'txn_id' => $txnId,
                'user_type' => 'customer',
                'creer' => $date,
                'modifier' => $date,
            ]);
        }

        return $txnId;
    }

    /**
     * Get available card plans dynamically from database table `tj_medical_card_plans`.
     */
    public function getPlans()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_medical_card_plans')) {
            $dbPlans = DB::table('tj_medical_card_plans')
                ->where('status', 'active')
                ->orderBy('id', 'asc')
                ->get();

            if ($dbPlans->count() > 0) {
                $formattedPlans = [];
                foreach ($dbPlans as $p) {
                    $benefits = [];
                    if (!empty($p->benefits)) {
                        $decoded = json_decode($p->benefits, true);
                        if (is_array($decoded)) {
                            $benefits = $decoded;
                        } else {
                            $benefits = array_values(array_filter(array_map('trim', explode("\n", (string)$p->benefits))));
                        }
                    }

                    $formattedPlans[] = [
                        'id' => $p->plan_code,
                        'db_id' => $p->id,
                        'title' => $p->title,
                        'badge' => $p->badge,
                        'price' => floatval($p->price),
                        'claim_limit' => floatval($p->claim_limit),
                        'max_claims' => intval($p->max_claims),
                        'period' => $p->period,
                        'benefits' => $benefits
                    ];
                }

                return response()->json([
                    'success' => 'Success',
                    'data' => $formattedPlans
                ]);
            }
        }

        // Hardcoded default fallback
        $plans = [
            [
                'id' => 'care_credit',
                'title' => 'CARE CREDIT',
                'price' => 1200,
                'claim_limit' => 5000,
                'max_claims' => 1,
                'period' => 'Single Claim',
                'benefits' => [
                  'One-time medical claim up to ₹5,000',
                  'Single claim only',
                  'Applicable for clinic, nursing home & medical store',
                  'After claiming, upgrade option opens'
                ]
            ],
            [
                'id' => 'opd_credit',
                'title' => 'OPD CREDIT',
                'price' => 2000,
                'claim_limit' => 15000,
                'max_claims' => 5,
                'period' => '1 Year',
                'benefits' => [
                  'Annual medical claim limit: ₹15,000',
                  'Valid for 1 Year',
                  'Maximum 5 claims',
                  'Applicable for clinic, nursing home & medical store',
                  'Reusable card'
                ]
            ],
            [
                'id' => 'medicash',
                'title' => 'MEDICASH',
                'price' => 3500,
                'claim_limit' => 10000,
                'max_claims' => 12,
                'period' => 'Monthly (12 Months)',
                'benefits' => [
                  'Monthly claim limit: ₹10,000',
                  '12 Months validity (Max ₹1,20,000)',
                  'Fresh ₹10,000 balance every month',
                  'Applicable for clinics, diagnostic labs & pharmacies'
                ]
            ]
        ];

        return response()->json([
            'success' => 'Success',
            'data' => $plans
        ]);
    }

    /**
     * Get payment settings including Razorpay Key.
     */
    public function getPaymentSettings()
    {
        $config = \App\Helpers\RazorpayConfig::resolve();
        return response()->json([
            'success' => 'Success',
            'data' => [
                'razorpay' => [
                    'key' => $config['key'] ?? '',
                    'enabled' => $config['is_enabled'] ?? true,
                ]
            ]
        ]);
    }

    /**
     * Purchase a Medical Cashback Card.
     */
    public function purchaseCard(Request $request)
    {
        $auth = $this->getAuthenticatedUserAndType($request);
        if (!$auth || empty($auth['user_id'])) {
            return response()->json(['success' => 'Failed', 'error' => 'User unauthenticated'], 401);
        }

        $userId = $auth['user_id'];
        $userType = $auth['user_type'];

        $validator = Validator::make($request->all(), [
            'card_type' => 'required|string',
            'aadhaar_number' => 'required|digits:12',
            'payment_method' => 'required|string', // wallet or razorpay
        ], [
            'aadhaar_number.digits' => 'Aadhaar Number must be exactly 12 digits.'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 'Failed', 'error' => $validator->errors()->first()], 400);
        }

        $cardType = strtoupper($request->input('card_type'));
        $paymentMethod = strtolower($request->input('payment_method'));
        $aadhaar = preg_replace('/\s+/', '', $request->input('aadhaar_number'));

        // Enforce strict Aadhaar uniqueness: No two active cards can share the same Aadhaar number
        $existingAadhaarCard = DB::table('tj_medical_cards')
            ->where('aadhaar_number', $aadhaar)
            ->where('status', 'active')
            ->first();

        if ($existingAadhaarCard) {
            return response()->json([
                'success' => 'Failed',
                'error' => "Aadhaar number {$aadhaar} is already linked to an active Medical Cashback Card. Duplicate card registration with the same Aadhaar is strictly prohibited."
            ], 400);
        }

        // Determine plan config by card type — price comes from frontend (what the user was shown)
        if (str_contains($cardType, 'CARE')) {
            $defaultPrice = 499;
            $limit = 5000;
            $maxClaims = 1;
            $title = 'CARE CREDIT';
        } elseif (str_contains($cardType, 'MEDICASH')) {
            $defaultPrice = 3500;
            $limit = 10000;
            $maxClaims = 12;
            $title = 'MEDICASH';
        } elseif (str_contains($cardType, 'DIGITAL')) {
            $defaultPrice = 499;
            $limit = 500000;
            $maxClaims = 1;
            $title = 'DIGITAL CREDIT ACCOUNT';
        } else {
            $defaultPrice = 999;
            $limit = 15000;
            $maxClaims = 5;
            $title = 'OPD CREDIT';
        }

        // Use price sent from frontend if provided and valid, else fallback to default
        $requestedPrice = $request->input('price');
        $price = (!empty($requestedPrice) && is_numeric($requestedPrice) && floatval($requestedPrice) > 0)
            ? floatval($requestedPrice)
            : $defaultPrice;

        DB::beginTransaction();
        try {
            // Handle Wallet Payment with M-PIN verification
            if ($paymentMethod === 'wallet') {
                $mPin = trim((string)$request->input('m_pin'));
                if (!$mPin || !preg_match('/^\d{4}$/', $mPin)) {
                    DB::rollBack();
                    return response()->json(['success' => 'Failed', 'error' => 'M-PIN must be exactly 4 digits'], 400);
                }

                if ($userType === 'driver') {
                    $account = DB::table('tj_conducteur')->where('id', $userId)->first();
                } else {
                    $account = DB::table('tj_user_app')->where('id', $userId)->first();
                }

                if (!$account) {
                    DB::rollBack();
                    return response()->json(['success' => 'Failed', 'error' => 'User account not found'], 404);
                }

                // Verify M-PIN
                $storedPin = $account->m_pin ?? $account->mdp ?? null;
                if ($storedPin && $storedPin !== $mPin && $storedPin !== md5($mPin)) {
                    DB::rollBack();
                    return response()->json(['success' => 'Failed', 'error' => 'Incorrect Wallet M-PIN entered'], 403);
                }

                $balance = floatval($account->amount ?? 0);
                if ($balance < $price) {
                    DB::rollBack();
                    return response()->json([
                        'success'    => 'Failed',
                        'error_code' => 'INSUFFICIENT_BALANCE',
                        'error'      => "Insufficient wallet balance (₹{$balance}). Please recharge wallet.",
                        'balance'    => $balance,
                        'required'   => $price,
                    ], 400);
                }

                // Deduct wallet balance & log ledger
                $txnId = 'MC_PURCHASE_' . time() . rand(100, 999);
                $this->recordWalletTransaction($userId, $userType, $price, 'debit', "Medical Card Purchase: {$title} - Ref #{$txnId}", $txnId);
            } else {
                $txnId = $request->input('razorpay_payment_id') ?? ('RZP_MC_' . time());
            }

            // Create or update active card
            $cardId = DB::table('tj_medical_cards')->insertGetId([
                'user_id' => $userId,
                'user_type' => $userType,
                'card_type' => $title,
                'aadhaar_number' => $aadhaar,
                'price' => $price,
                'claim_limit' => $limit,
                'used_amount' => 0,
                'remaining_amount' => $limit,
                'claims_count' => 0,
                'max_claims' => $maxClaims,
                'payment_method' => $paymentMethod,
                'payment_txn_id' => $txnId,
                'status' => 'active',
                'expires_at' => now()->addYear(),
                'creer' => now(),
                'modifier' => now()
            ]);

            DB::commit();

            // Trigger dynamic referral cashback reward based on admin rules
            try {
                \App\Services\ReferralRewardService::processReward(
                    $userId,
                    $userType,
                    'medical_cashback',
                    $price,
                    "Medical Card ({$title})"
                );
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error("Medical Cashback referral reward error: " . $ex->getMessage());
            }

            $activeCard = DB::table('tj_medical_cards')->where('id', $cardId)->first();

            return response()->json([
                'success' => 'Success',
                'message' => "Medical Card '{$title}' activated successfully!",
                'data' => $activeCard
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => 'Failed', 'error' => 'Failed to process card purchase: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get user's active medical card and expense summary.
     */
    public function getMyCard(Request $request)
    {
        $auth = $this->getAuthenticatedUserAndType($request);
        $userId = $auth['user_id'] ?? null;
        $userType = $auth['user_type'] ?? 'customer';

        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'User unauthenticated'], 401);
        }

        $card = DB::table('tj_medical_cards')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();

        $expenses = DB::table('tj_medical_expenses')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        $claims = DB::table('tj_medical_claims')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        $userProfile = null;

        if ($userType === 'driver') {
            $person = DB::table('tj_conducteur')->where('id', $userId)->first();
            if ($person) {
                $userProfile = [
                    'id' => (string)$person->id,
                    'name' => trim(($person->prenom ?? '') . ' ' . ($person->nom ?? '')) ?: 'Driver #' . $person->id,
                    'phone' => (string)($person->phone ?? ''),
                    'wallet_balance' => floatval($person->amount ?? 0),
                    'user_type' => 'driver'
                ];
            }
        } else {
            $person = DB::table('tj_user_app')->where('id', $userId)->first();
            if ($person) {
                $userProfile = [
                    'id' => (string)$person->id,
                    'name' => trim(($person->prenom ?? '') . ' ' . ($person->nom ?? '')) ?: 'User #' . $person->id,
                    'phone' => (string)($person->phone ?? ''),
                    'wallet_balance' => floatval($person->amount ?? 0),
                    'user_type' => 'customer'
                ];
            }
        }

        return response()->json([
            'success' => 'Success',
            'data' => [
                'user_profile' => $userProfile,
                'card' => $card,
                'total_tracked_expenses' => $totalExpenses,
                'expenses' => $expenses,
                'claims' => $claims
            ]
        ]);
    }

    /**
     * Submit a new Medical Cashback Claim.
     */
    public function submitClaim(Request $request)
    {
        $auth = $this->getAuthenticatedUserAndType($request);
        $userId = $auth['user_id'] ?? null;
        $userType = $auth['user_type'] ?? 'customer';

        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'User unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 'Failed', 'error' => $validator->errors()->first()], 400);
        }

        $card = DB::table('tj_medical_cards')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('user_id', strval($userId));
            })
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->first();

        if (!$card) {
            return response()->json(['success' => 'Failed', 'error' => 'No active Medical Cashback Card found. Please purchase a plan first.'], 400);
        }

        $remainingLimit = floatval($card->remaining_amount ?? $card->claim_limit ?? 0);
        $totalLimit = floatval($card->claim_limit ?? 0);
        $amount = floatval($request->input('amount'));

        if ($amount <= 0) {
            return response()->json(['success' => 'Failed', 'error' => 'Please enter a valid expense amount.'], 400);
        }

        if ($amount > $remainingLimit) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Claim amount ₹' . number_format($amount, 2) . ' exceeds your remaining card claim limit of ₹' . number_format($remainingLimit, 2) . '.'
            ], 400);
        }

        // Enforce maximum claims count constraint (e.g. max_claims = 1)
        $existingClaimsCount = DB::table('tj_medical_claims')
            ->where('card_id', $card->id)
            ->whereIn('status', ['under_review', 'pending', 'approved', 'need_reupload'])
            ->count();

        $maxClaims = intval($card->max_claims ?? 1);
        if ($maxClaims > 0 && ($existingClaimsCount >= $maxClaims || intval($card->claims_count ?? 0) >= $maxClaims || $card->status === 'exhausted')) {
            return response()->json([
                'success' => 'Failed',
                'error' => "You have reached the maximum allowed claims ({$existingClaimsCount}/{$maxClaims}) for your current card plan. Please purchase a new card plan to continue."
            ], 400);
        }

        // Upload documents helper
        $prescriptionPath = $this->uploadFile($request, 'prescription');
        $diagnosticPath = $this->uploadFile($request, 'diagnostic');
        $cashMemoPath = $this->uploadFile($request, 'cash_memo');

        $claimId = 'CLM' . time() . rand(10, 99);

        DB::table('tj_medical_claims')->insert([
            'claim_id' => $claimId,
            'user_id' => $userId,
            'user_type' => $userType,
            'card_id' => $card->id,
            'card_type' => $card->card_type,
            'expense_amount' => $amount,
            'requested_amount' => $amount,
            'approved_amount' => 0,
            'status' => 'under_review',
            'prescription_doc' => $prescriptionPath ?? 'Prescription.jpg',
            'diagnostic_doc' => $diagnosticPath ?? 'Lab Report.pdf',
            'cash_memo_doc' => $cashMemoPath ?? 'Cash Memo.jpg',
            'creer' => now(),
            'modifier' => now()
        ]);

        return response()->json([
            'success' => 'Success',
            'message' => 'Claim submitted successfully! Documents are under review.',
            'claim_id' => $claimId
        ]);
    }

    /**
     * Resubmit documents for a claim that needs reupload.
     */
    public function reuploadClaim(Request $request, $claimId)
    {
        $auth = $this->getAuthenticatedUserAndType($request);
        $userId = $auth['user_id'] ?? null;
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'User unauthenticated'], 401);
        }

        $claim = DB::table('tj_medical_claims')
            ->where('claim_id', $claimId)
            ->where('user_id', $userId)
            ->first();

        if (!$claim) {
            return response()->json(['success' => 'Failed', 'error' => 'Claim not found'], 404);
        }

        $prescriptionPath = $this->uploadFile($request, 'prescription') ?? $claim->prescription_doc;
        $diagnosticPath = $this->uploadFile($request, 'diagnostic') ?? $claim->diagnostic_doc;
        $cashMemoPath = $this->uploadFile($request, 'cash_memo') ?? $claim->cash_memo_doc;

        DB::table('tj_medical_claims')
            ->where('claim_id', $claimId)
            ->update([
                'prescription_doc' => $prescriptionPath,
                'diagnostic_doc' => $diagnosticPath,
                'cash_memo_doc' => $cashMemoPath,
                'status' => 'under_review',
                'reupload_reason' => null,
                'modifier' => now()
            ]);

        return response()->json([
            'success' => 'Success',
            'message' => 'Updated documents resubmitted successfully.'
        ]);
    }

    private function uploadFile(Request $request, $key)
    {
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $uploadDir = public_path('uploads/medical_docs');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $filename = time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            return 'uploads/medical_docs/' . $filename;
        }
        return null;
    }
}
