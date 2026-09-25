<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceLoanApplication extends Model
{
    protected $table = 'finance_loan_applications';

    protected $fillable = [
        'application_number',
        'customer_id',
        'product_id',
        'user_type',
        'applicant_name',
        'applicant_phone',
        'loan_category',
        'requested_amount',
        'indicative_amount',
        'approved_amount',
        'tenure_months',
        'estimated_emi',
        'estimated_total_repayment',
        'processing_fee_amount',
        'processing_fee_tax',
        'processing_fee_total',
        'processing_fee_status',
        'processing_fee_payment_method',
        'processing_fee_txn_id',
        'selected_lender_id',
        'selected_lender_name',
        'partner_selection_time',
        'process_completion_proof_url',
        'proof_submitted_at',
        'proof_remarks',
        'agent_selfie_url',
        'agent_selfie_submitted_at',
        'disbursement_bank_name',
        'disbursement_account_name',
        'disbursement_account_number',
        'disbursement_ifsc',
        'disbursement_account_type',
        'disbursement_status',
        'disbursement_txn_ref',
        'disbursed_at',
        'application_status',
        'rejection_reason',
        'reapply_locked_until',
        'admin_remarks',
        'business_details',
        'student_details',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'indicative_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'estimated_emi' => 'decimal:2',
        'estimated_total_repayment' => 'decimal:2',
        'processing_fee_amount' => 'decimal:2',
        'processing_fee_tax' => 'decimal:2',
        'processing_fee_total' => 'decimal:2',
        'partner_selection_time' => 'datetime',
        'proof_submitted_at' => 'datetime',
        'agent_selfie_submitted_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'reapply_locked_until' => 'datetime',
        'business_details' => 'array',
        'student_details' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(FinanceLoanProduct::class, 'product_id');
    }

    public function lender(): BelongsTo
    {
        return $this->belongsTo(FinanceLenderPartner::class, 'selected_lender_id');
    }

    public function dailySchedules(): HasMany
    {
        return $this->hasMany(FinanceDailySchedule::class, 'application_id');
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(FinanceDocumentRequest::class, 'application_id');
    }
}
