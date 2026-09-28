<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDocument extends Model
{
    protected $table = 'finance_documents';

    protected $fillable = [
        'customer_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'status',
        'verified_at',
        'verified_by',
        'admin_remark',
        'is_reusable',
        'reuse_valid_until',
        'expires_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'reuse_valid_until' => 'datetime',
        'expires_at' => 'datetime',
        'is_reusable' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }

    public function isReusable(): bool
    {
        if (!$this->is_reusable) {
            return false;
        }
        if ($this->status === 'rejected') {
            return false;
        }
        if ($this->reuse_valid_until && now()->gt($this->reuse_valid_until)) {
            return false;
        }
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }
        return true;
    }

    public function getValidUntilAttribute()
    {
        return $this->reuse_valid_until ?? $this->expires_at ?? ($this->created_at ? $this->created_at->copy()->addDays(5) : now()->addDays(5));
    }

    public function getUrlAttribute(): string
    {
        if (empty($this->file_path)) return '';
        if (str_starts_with($this->file_path, 'http')) return $this->file_path;
        return asset('storage/' . ltrim($this->file_path, '/'));
    }
}
