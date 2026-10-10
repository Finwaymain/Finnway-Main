<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class FinanceAffiliateLender extends Model
{
    protected $table = 'finance_affiliate_lenders';

    protected $fillable = [
        'name',
        'logo',
        'min_loan_amount',
        'max_loan_amount',
        'interest_rate_display',
        'tenure_display',
        'processing_fee_display',
        'affiliate_url',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'min_loan_amount' => 'decimal:2',
        'max_loan_amount' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function leads()
    {
        return $this->hasMany(FinanceLenderLead::class, 'lender_id');
    }
}
