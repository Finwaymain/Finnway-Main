<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserApp;
use App\Models\Driver;

class FinanceCustomer extends Model
{
    protected $table = 'finance_customers';

    protected $fillable = [
        'user_type',
        'user_id',
        'driver_id',
        'phone',
        'name',
        'email',
        'dob',
        'gender',
        'pan',
        'aadhaar',
        'address',
        'city',
        'state',
        'pincode',
        'employment_type',
        'company_name',
        'monthly_income',
        'existing_emi',
        'cibil_category',
        'kyc_status',
        'kyc_verified_at',
        'account_status',
    ];

    protected $casts = [
        'dob' => 'date',
        'monthly_income' => 'decimal:2',
        'existing_emi' => 'decimal:2',
        'kyc_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(UserApp::class, 'user_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FinanceDocument::class, 'customer_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(FinanceLoanApplication::class, 'customer_id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(FinanceWallet::class, 'customer_id');
    }

    public function dailySchedules(): HasMany
    {
        return $this->hasMany(FinanceDailySchedule::class, 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'customer_id');
    }

    /**
     * Smart Document Reuse Engine (Doc 4: 3-5 day validity window)
     */
    public function getReusableDocument(string $docType, int $validityDays = 5): ?FinanceDocument
    {
        return $this->documents()
            ->where('document_type', $docType)
            ->where('status', 'verified')
            ->where('is_reusable', true)
            ->where('created_at', '>=', now()->subDays($validityDays))
            ->latest('id')
            ->first();
    }
}
