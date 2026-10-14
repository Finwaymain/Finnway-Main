<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTopupCashbackRule extends Model
{
    protected $table = 'wallet_topup_cashback_rules';

    protected $fillable = [
        'name',
        'cashback_type',
        'cashback_value',
        'min_topup_amount',
        'max_topup_amount',
        'max_cashback_amount',
        'target_user_type',
        'usage_limit_per_user',
        'priority',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'cashback_value'       => 'float',
        'min_topup_amount'     => 'float',
        'max_topup_amount'     => 'float',
        'max_cashback_amount'  => 'float',
        'usage_limit_per_user' => 'integer',
        'priority'             => 'integer',
        'is_active'            => 'boolean',
        'start_date'           => 'datetime',
        'end_date'             => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(WalletTopupCashbackLog::class, 'rule_id');
    }
}
