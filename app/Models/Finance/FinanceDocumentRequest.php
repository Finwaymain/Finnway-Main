<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDocumentRequest extends Model
{
    protected $table = 'finance_document_requests';

    protected $fillable = [
        'customer_id',
        'application_id',
        'requested_documents',
        'admin_remark',
        'status',
        'created_by',
    ];

    protected $casts = [
        'requested_documents' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(FinanceCustomer::class, 'customer_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FinanceLoanApplication::class, 'application_id');
    }
}
