<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceTransaction extends Model
{
    protected $table = 'finance_transactions';

    protected $fillable = [
        'customer_id',
        'application_id',
        'wallet_id',
        'txn_number',
        'txn_type',
        'amount',
        'direction',
        'balance_after',
        'payment_method',
        'payment_gateway_ref',
        'receiver_name',
        'receiver_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FinanceLoanApplication::class, 'application_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(FinanceWallet::class, 'wallet_id');
    }
}
