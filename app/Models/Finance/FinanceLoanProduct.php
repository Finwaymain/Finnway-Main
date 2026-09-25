<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceLoanProduct extends Model
{
    protected $table = 'finance_loan_products';

    protected $fillable = [
        'code',
        'name',
        'category',
        'min_amount',
        'max_amount',
        'min_tenure_months',
        'max_tenure_months',
        'interest_rate_p_a',
        'is_interest_free',
        'processing_fee_type',
        'processing_fee_value',
        'processing_fee_slabs',
        'daily_repayment_amount',
        'daily_usage_limit',
        'grace_period_days',
        'late_fee',
        'penalty_rate',
        'auto_verify',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate_p_a' => 'decimal:2',
        'is_interest_free' => 'boolean',
        'processing_fee_value' => 'decimal:2',
        'processing_fee_slabs' => 'array',
        'daily_repayment_amount' => 'decimal:2',
        'daily_usage_limit' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'auto_verify' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(FinanceLoanApplication::class, 'product_id');
    }
}
