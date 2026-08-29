<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalCashbackAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin Claims Queue & Verification Dashboard
     */
    public function index(Request $request)
    {
        $this->ensurePlansTableExists();

        $status = $request->query('status', 'all');

        $query = DB::table('tj_medical_claims as c')
            ->select('c.*');

        if ($status !== 'all') {
            $query->where('c.status', $status);
        }

        $claims = $query->orderBy('c.id', 'desc')->paginate(15);

        // Enrich claims with user/driver name & phone
        foreach ($claims as $claim) {
            if ($claim->user_type === 'driver') {
                $person = DB::table('tj_conducteur')->where('id', $claim->user_id)->first();
                $claim->user_name = trim(($person->prenom ?? '') . ' ' . ($person->nom ?? '')) ?: 'Driver #' . $claim->user_id;
                $claim->user_phone = $person->phone ?? 'N/A';
            } else {
                $person = DB::table('tj_user_app')->where('id', $claim->user_id)->first();
                $claim->user_name = trim(($person->prenom ?? '') . ' ' . ($person->nom ?? '')) ?: 'User #' . $claim->user_id;
                $claim->user_phone = $person->phone ?? 'N/A';
            }
        }

        // Stats counts
        $stats = [
            'total' => DB::table('tj_medical_claims')->count(),
            'pending' => DB::table('tj_medical_claims')->whereIn('status', ['pending', 'under_review'])->count(),
            'approved' => DB::table('tj_medical_claims')->where('status', 'approved')->count(),
            'reupload' => DB::table('tj_medical_claims')->where('status', 'need_reupload')->count(),
            'rejected' => DB::table('tj_medical_claims')->where('status', 'rejected')->count(),
            'total_approved_amount' => DB::table('tj_medical_claims')->where('status', 'approved')->sum('approved_amount'),
        ];

        return view('medical_cashback.index', compact('claims', 'stats', 'status'));
    }

    /**
     * View List of Purchased Active Cards
     */
    public function cards(Request $request)
    {
        $cards = DB::table('tj_medical_cards')
            ->orderBy('id', 'desc')
            ->paginate(15);

        foreach ($cards as $card) {
            if ($card->user_type === 'driver') {
                $person = DB::table('tj_conducteur')->where('id', $card->user_id)->first();
                $card->user_name = trim(($person->prenom ?? '') . ' ' . ($person->nom ?? '')) ?: 'Driver #' . $card->user_id;
                $card->user_phone = $person->phone ?? 'N/A';
            } else {
                $person = DB::table('tj_user_app')->where('id', $card->user_id)->first();
                $card->user_name = trim(($person->prenom ?? '') . ' ' . ($person->nom ?? '')) ?: 'User #' . $card->user_id;
                $card->user_phone = $person->phone ?? 'N/A';
            }
        }

        return view('medical_cashback.cards', compact('cards'));
    }

    /**
     * Approve Claim and Credit Wallet
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'approved_amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string',
        ]);

        $claim = DB::table('tj_medical_claims')->where('id', $id)->first();
        if (!$claim) {
            return back()->with('error', 'Claim record not found');
        }

        if ($claim->status === 'approved') {
            return back()->with('error', 'Claim is already approved');
        }

        $approvedAmount = floatval($request->input('approved_amount'));
        $reason = $request->input('reason', 'Medical claim verified and approved by admin.');

        DB::beginTransaction();
        try {
            $txnId = 'MC_CREDIT_' . time() . rand(100, 999);
            $date = now()->toDateTimeString();

            // Credit Wallet & log transaction
            if ($claim->user_type === 'driver') {
                $driver = DB::table('tj_conducteur')->where('id', $claim->user_id)->first();
                $newBalance = floatval($driver->amount ?? 0) + $approvedAmount;
                DB::table('tj_conducteur')->where('id', $claim->user_id)->update(['amount' => $newBalance]);

                DB::table('tj_conducteur_transaction')->insert([
                    'id_conducteur' => $claim->user_id,
                    'amount' => $approvedAmount,
                    'deduction_type' => 'credit',
                    'payment_method' => 'Medical Cashback',
                    'payment_status' => 'success',
                    'date' => $date,
                    'description' => "Medical Cashback Credited: Claim #{$claim->claim_id}",
                    'txn_id' => $txnId,
                    'creer' => $date,
                    'modifier' => $date,
                ]);
            } else {
                $user = DB::table('tj_user_app')->where('id', $claim->user_id)->first();
                $newBalance = floatval($user->amount ?? 0) + $approvedAmount;
                DB::table('tj_user_app')->where('id', $claim->user_id)->update(['amount' => $newBalance]);

                DB::table('tj_transaction')->insert([
                    'id_user_app' => $claim->user_id,
                    'amount' => $approvedAmount,
                    'deduction_type' => 'credit',
                    'payment_method' => 'Medical Cashback',
                    'payment_status' => 'success',
                    'date' => $date,
                    'description' => "Medical Cashback Credited: Claim #{$claim->claim_id}",
                    'txn_id' => $txnId,
                    'user_type' => 'customer',
                    'creer' => $date,
                    'modifier' => $date,
                ]);
            }

            // Update claim status
            DB::table('tj_medical_claims')->where('id', $id)->update([
                'status' => 'approved',
                'approved_amount' => $approvedAmount,
                'approval_reason' => $reason,
                'wallet_txn_id' => $txnId,
                'settled_at' => now(),
                'modifier' => now()
            ]);

            // Update card used & remaining balance if card exists
            if ($claim->card_id) {
                $card = DB::table('tj_medical_cards')->where('id', $claim->card_id)->first();
                if ($card) {
                    $newUsed = floatval($card->used_amount) + $approvedAmount;
                    $newRemaining = max(0, floatval($card->claim_limit) - $newUsed);
                    $newClaimsCount = intval($card->claims_count) + 1;
                    $newStatus = ($newRemaining <= 0 || $newClaimsCount >= intval($card->max_claims)) ? 'exhausted' : $card->status;

                    DB::table('tj_medical_cards')->where('id', $claim->card_id)->update([
                        'used_amount' => $newUsed,
                        'remaining_amount' => $newRemaining,
                        'claims_count' => $newClaimsCount,
                        'status' => $newStatus,
                        'modifier' => now()
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', "Claim #{$claim->claim_id} approved successfully! ₹{$approvedAmount} credited to wallet.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve claim: ' . $e->getMessage());
        }
    }

    /**
     * Request Reupload for Claim
     */
    public function reupload(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $claim = DB::table('tj_medical_claims')->where('id', $id)->first();
        if (!$claim) {
            return back()->with('error', 'Claim record not found');
        }

        DB::table('tj_medical_claims')->where('id', $id)->update([
            'status' => 'need_reupload',
            'reupload_reason' => $request->input('reason'),
            'modifier' => now()
        ]);

        return back()->with('success', "Claim #{$claim->claim_id} flagged for document reupload.");
    }

    /**
     * Reject Claim
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $claim = DB::table('tj_medical_claims')->where('id', $id)->first();
        if (!$claim) {
            return back()->with('error', 'Claim record not found');
        }

        DB::table('tj_medical_claims')->where('id', $id)->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'modifier' => now()
        ]);

        return back()->with('success', "Claim #{$claim->claim_id} has been rejected.");
    }

    /**
     * Ensure table `tj_medical_card_plans` exists on the server
     */
    private function ensurePlansTableExists()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_medical_card_plans')) {
            try {
                \Illuminate\Support\Facades\Schema::create('tj_medical_card_plans', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('plan_code', 50)->unique();
                    $table->string('title', 100);
                    $table->string('badge', 50)->nullable();
                    $table->decimal('price', 10, 2);
                    $table->decimal('claim_limit', 10, 2);
                    $table->integer('max_claims')->default(1);
                    $table->string('period', 50)->default('1 Year');
                    $table->text('benefits')->nullable();
                    $table->string('status', 20)->default('active');
                    $table->dateTime('creer')->nullable();
                    $table->dateTime('modifier')->nullable();
                });

                $now = date('Y-m-d H:i:s');

                DB::table('tj_medical_card_plans')->insert([
                    [
                        'plan_code' => 'care_credit',
                        'title' => 'CARE CREDIT',
                        'badge' => '★ Most Popular',
                        'price' => 1200,
                        'claim_limit' => 5000,
                        'max_claims' => 1,
                        'period' => 'Single Claim',
                        'benefits' => json_encode([
                            'One-time medical claim up to ₹5,000',
                            'Single claim only',
                            'Applicable for clinic, nursing home & medical store',
                            'After claiming, upgrade option opens'
                        ]),
                        'status' => 'active',
                        'creer' => $now,
                        'modifier' => $now
                    ],
                    [
                        'plan_code' => 'opd_credit',
                        'title' => 'OPD CREDIT',
                        'badge' => null,
                        'price' => 2000,
                        'claim_limit' => 15000,
                        'max_claims' => 5,
                        'period' => '1 Year',
                        'benefits' => json_encode([
                            'Annual medical claim limit: ₹15,000',
                            'Valid for 1 Year',
                            'Maximum 5 claims',
                            'Applicable for clinic, nursing home & medical store',
                            'Reusable card'
                        ]),
                        'status' => 'active',
                        'creer' => $now,
                        'modifier' => $now
                    ],
                    [
                        'plan_code' => 'medicash',
                        'title' => 'MEDICASH',
                        'badge' => null,
                        'price' => 3500,
                        'claim_limit' => 10000,
                        'max_claims' => 12,
                        'period' => '12 Months',
                        'benefits' => json_encode([
                            'Monthly claim limit: ₹10,000',
                            '12 Months validity (Max ₹1,20,000)',
                            'Fresh ₹10,000 balance every month',
                            'Applicable for clinics, diagnostic labs & pharmacies'
                        ]),
                        'status' => 'active',
                        'creer' => $now,
                        'modifier' => $now
                    ]
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to auto-create tj_medical_card_plans table: ' . $e->getMessage());
            }
        }
    }

    /**
     * Manage Card Plans (Admin Screen to Add, Edit, Change Status, Delete Cards - NO MODALS)
     */
    public function managePlans(Request $request)
    {
        $this->ensurePlansTableExists();

        $plans = DB::table('tj_medical_card_plans')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($plans as $plan) {
            if (!empty($plan->benefits)) {
                $decoded = json_decode($plan->benefits, true);
                $plan->benefits_list = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", (string)$plan->benefits))));
            } else {
                $plan->benefits_list = [];
            }
        }

        $editPlan = null;
        if ($request->has('edit_id')) {
            $editPlan = DB::table('tj_medical_card_plans')->where('id', $request->query('edit_id'))->first();
            if ($editPlan) {
                if (!empty($editPlan->benefits)) {
                    $decoded = json_decode($editPlan->benefits, true);
                    $editPlan->benefits_text = is_array($decoded) ? implode("\n", $decoded) : (string)$editPlan->benefits;
                } else {
                    $editPlan->benefits_text = '';
                }
            }
        }

        return view('medical_cashback.plans', compact('plans', 'editPlan'));
    }

    /**
     * Store New Card Plan
     */
    public function storePlan(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'claim_limit' => 'required|numeric|min:0',
            'max_claims' => 'required|integer|min:1',
            'period' => 'required|string',
        ]);

        $title = trim($request->input('title'));
        $planCode = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $title)) . '_' . time();

        $benefitsArray = array_values(array_filter(array_map('trim', explode("\n", $request->input('benefits', '')))));

        $now = now()->toDateTimeString();

        DB::table('tj_medical_card_plans')->insert([
            'plan_code' => $planCode,
            'title' => strtoupper($title),
            'badge' => $request->input('badge'),
            'price' => floatval($request->input('price')),
            'claim_limit' => floatval($request->input('claim_limit')),
            'max_claims' => intval($request->input('max_claims')),
            'period' => $request->input('period'),
            'benefits' => json_encode($benefitsArray),
            'status' => $request->input('status', 'active'),
            'creer' => $now,
            'modifier' => $now
        ]);

        return back()->with('success', "Card plan '{$title}' created successfully!");
    }

    /**
     * Update Card Plan
     */
    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'claim_limit' => 'required|numeric|min:0',
            'max_claims' => 'required|integer|min:1',
            'period' => 'required|string',
        ]);

        $benefitsArray = array_values(array_filter(array_map('trim', explode("\n", $request->input('benefits', '')))));

        DB::table('tj_medical_card_plans')->where('id', $id)->update([
            'title' => strtoupper(trim($request->input('title'))),
            'badge' => $request->input('badge'),
            'price' => floatval($request->input('price')),
            'claim_limit' => floatval($request->input('claim_limit')),
            'max_claims' => intval($request->input('max_claims')),
            'period' => $request->input('period'),
            'benefits' => json_encode($benefitsArray),
            'status' => $request->input('status', 'active'),
            'modifier' => now()
        ]);

        return back()->with('success', "Card plan updated successfully!");
    }

    /**
     * Toggle Card Plan Status (active <-> inactive)
     */
    public function togglePlanStatus($id)
    {
        $plan = DB::table('tj_medical_card_plans')->where('id', $id)->first();
        if (!$plan) {
            return back()->with('error', 'Card plan not found');
        }

        $newStatus = ($plan->status === 'active') ? 'inactive' : 'active';

        DB::table('tj_medical_card_plans')->where('id', $id)->update([
            'status' => $newStatus,
            'modifier' => now()
        ]);

        return back()->with('success', "Card plan '{$plan->title}' status changed to " . strtoupper($newStatus));
    }

    /**
     * Delete Card Plan
     */
    public function deletePlan($id)
    {
        $plan = DB::table('tj_medical_card_plans')->where('id', $id)->first();
        if (!$plan) {
            return back()->with('error', 'Card plan not found');
        }

        DB::table('tj_medical_card_plans')->where('id', $id)->delete();

        return back()->with('success', "Card plan '{$plan->title}' deleted successfully.");
    }
}
