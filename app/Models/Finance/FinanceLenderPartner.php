<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class FinanceLenderPartner extends Model
{
    protected $table = 'finance_lender_partners';

    protected $fillable = [
        'name',
        'logo',
        'loan_types',
        'min_loan_amount',
        'max_loan_amount',
        'interest_rate_display',
        'tenure_display',
        'processing_fee_display',
        'application_url',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'loan_types' => 'array',
        'min_loan_amount' => 'decimal:2',
        'max_loan_amount' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
