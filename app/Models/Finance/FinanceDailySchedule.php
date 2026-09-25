<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDailySchedule extends Model
{
    protected $table = 'finance_daily_schedules';

    protected $fillable = [
        'application_id',
        'customer_id',
        'schedule_date',
        'day_number',
        'emi_amount',
        'late_charges',
        'penalty_amount',
        'total_due',
        'paid_amount',
        'status',
        'paid_at',
        'payment_method',
        'txn_id',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'emi_amount' => 'decimal:2',
        'late_charges' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_due' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FinanceLoanApplication::class, 'application_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }
}
