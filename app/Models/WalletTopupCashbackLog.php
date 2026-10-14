<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTopupCashbackLog extends Model
{
    protected $table = 'wallet_topup_cashback_logs';

    protected $fillable = [
        'rule_id',
        'user_id',
        'user_type',
        'topup_amount',
        'cashback_amount',
        'txn_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'topup_amount'    => 'float',
        'cashback_amount' => 'float',
    ];

    public function rule()
    {
        return $this->belongsTo(WalletTopupCashbackRule::class, 'rule_id');
    }
}
