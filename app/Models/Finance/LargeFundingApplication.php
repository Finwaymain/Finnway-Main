<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LargeFundingApplication extends Model
{
    use HasFactory;

    protected $table = 'large_funding_applications';

    protected $guarded = ['id'];

    protected $casts = [
        'is_otp_verified'               => 'boolean',
        'founders_json'                 => 'array',
        'consent_info_accurate'         => 'boolean',
        'consent_fiinway_review'        => 'boolean',
        'consent_share_with_investors'  => 'boolean',
        'consent_terms_policy'          => 'boolean',
        'consent_no_guarantee'          => 'boolean',
        'submitted_at'                  => 'datetime',
        'fee_deadline'                  => 'datetime',
        'fee_paid_at'                   => 'datetime',
        'disbursed_at'                  => 'datetime',
        'service_fee_base'              => 'decimal:2',
        'service_fee_gst'               => 'decimal:2',
        'service_fee_total'             => 'decimal:2',
        'approved_amount'               => 'decimal:2',
        'registration_date'             => 'date',
    ];

    public function documents()
    {
        return $this->hasMany(LargeFundingDocument::class, 'application_id');
    }

    public function customer()
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\UserApp::class, 'user_id');
    }

    /**
     * Map current stage number to human readable milestone text
     */
    public function getStageLabelAttribute(): string
    {
        return match ((int)$this->current_stage) {
            1 => 'Application Submitted',
            2 => 'Documents & Project Review',
            3 => 'Service Fee Payment',
            4 => 'Forwarded to Investors / Lenders',
            5 => 'Funding Discussion',
            6 => 'Final Offer & Approval',
            7 => 'Funding Approved',
            8 => 'Amount Credited',
            default => 'Under Review',
        };
    }
}
