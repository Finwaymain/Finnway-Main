<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class FinanceLenderLead extends Model
{
    protected $table = 'finance_lender_leads';

    protected $fillable = [
        'lender_id',
        'lender_name',
        'applicant_name',
        'phone',
        'email',
        'referral_code',
        'referrer_type',
        'referrer_id',
        'affiliate_url',
        'ip_address',
        'user_agent',
    ];

    public function lender()
    {
        return $this->belongsTo(FinanceAffiliateLender::class, 'lender_id');
    }

    public function partnerLender()
    {
        return $this->belongsTo(FinanceLenderPartner::class, 'lender_id');
    }
}
