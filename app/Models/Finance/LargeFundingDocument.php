<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LargeFundingDocument extends Model
{
    use HasFactory;

    protected $table = 'large_funding_documents';

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(LargeFundingApplication::class, 'application_id');
    }

    /**
     * Readable document label
     */
    public function getDocumentNameAttribute(): string
    {
        $types = [
            'bank_cheque'      => 'Bank Cancelled Cheque / Statement',
            'pan_card'         => 'Main Applicant PAN Card',
            'identity_proof'   => 'Valid Identity Proof (Aadhaar / Passport)',
            'pitch_deck'       => 'Business Plan / Pitch Deck',
            'fund_plan'        => 'Fund Utilisation Plan',
            'company_reg'      => 'Company Registration Certificate',
            'gst_cert'         => 'GST Certificate',
            'itr_financials'   => 'ITR / Financial Statements',
            'cap_table'        => 'Shareholding Pattern / Cap Table',
            'other'            => 'Other Supporting Document',
        ];

        return $types[$this->document_type] ?? ucwords(str_replace('_', ' ', $this->document_type));
    }
}
