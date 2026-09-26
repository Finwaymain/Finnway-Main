<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceWallet extends Model
{
    protected $table = 'finance_wallets';

    protected $fillable = [
        'customer_id',
        'wallet_type',
        'approved_limit',
        'available_balance',
        'used_amount',
        'daily_usage_limit',
        'used_today',
        'today_usage_permission',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'approved_limit' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'daily_usage_limit' => 'decimal:2',
        'used_today' => 'decimal:2',
        'valid_until' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }

    /**
     * Checks if today's usage limit is available and unlocked
     */
    public function canSpendToday(float $amount): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->today_usage_permission !== 'ACTIVE') return false;
        if ($this->available_balance < $amount) return false;

        $remainingToday = max(0, $this->daily_usage_limit - $this->used_today);
        return $amount <= $remainingToday;
    }

    public function getCreditLimitAttribute() { return $this->approved_limit; }
    public function getAvailableLimitAttribute() { return $this->available_balance; }
    public function getDailyLimitAttribute() { return $this->daily_usage_limit; }
    public function getUsedLimitAttribute() { return $this->used_amount; }
}
