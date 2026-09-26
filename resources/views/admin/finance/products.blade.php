@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        ADMIN CONTROL &amp; POLICY
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Loan Products &amp; Fee Governance</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Loan Products &amp; Processing Fee Policy
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Dashboard
                </a>
                <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Applications
                </a>
                <a href="{{ route('admin.finance.recovery') }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px;">
                    Daily Recovery
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; border-left: 4px solid #059669 !important;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #fef2f2; color: #991b1b; font-size: 13px; font-weight: 600; border-left: 4px solid #dc2626 !important;" role="alert">
            <strong>✕ Validation Error:</strong> Please check the form errors below.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row g-4">
            <!-- Products Table -->
            <div class="col-lg-8">
                <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Active Loan Products &amp; Pricing Policies</h5>
                        <div style="font-size: 12px; color: #64748b;">Admin directly controls processing fees, slab structures, loan bounds, and daily caps.</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 13px;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                                    <th style="padding: 12px 16px;">Product / Code</th>
                                    <th style="padding: 12px 16px;">Category</th>
                                    <th style="padding: 12px 16px;">Processing Fee</th>
                                    <th style="padding: 12px 16px;">Limits &amp; Interest</th>
                                    <th style="padding: 12px 16px;">Daily Cap</th>
                                    <th style="padding: 12px 16px;">Status</th>
                                    <th style="padding: 12px 16px; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $prod)
                                <tr style="border-top: 1px solid #f1f5f9; vertical-align: middle;">
                                    <td style="padding: 12px 16px;">
                                        <div style="font-weight: 700; color: #0f172a;">{{ $prod->name }}</div>
                                        <code style="font-size: 11px; color: #1e3a8a; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">{{ $prod->code }}</code>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 11px; text-transform: uppercase;">
                                            {{ str_replace('_', ' ', $prod->category) }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <div style="font-weight: 700; color: #0284c7;">
                                            @if($prod->processing_fee_type === 'percentage')
                                                {{ $prod->processing_fee_value }}% of Loan
                                            @else
                                                ₹{{ number_format($prod->processing_fee_value, 2) }}
                                            @endif
                                        </div>
                                        @if(!empty($prod->processing_fee_slabs))
                                            <span class="badge" style="background: #ede9fe; color: #5b21b6; font-size: 10px;">
                                                {{ count($prod->processing_fee_slabs) }} Slabs Configured
                                            </span>
                                        @else
                                            <span style="font-size: 11px; color: #64748b;">+ 18% GST</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <div style="font-weight: 600; color: #0f172a;">
                                            ₹{{ number_format($prod->min_amount) }} – ₹{{ number_format($prod->max_amount) }}
                                        </div>
                                        <div style="font-size: 11px; color: #64748b;">
                                            @if($prod->is_interest_free || (float)$prod->interest_rate_p_a == 0)
                                                <span class="badge" style="background: #ecfdf5; color: #065f46;">0% Interest</span>
                                            @else
                                                {{ $prod->interest_rate_p_a }}% p.a.
                                            @endif
                                            • {{ $prod->min_tenure_months }}–{{ $prod->max_tenure_months }} Mo
                                        </div>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        @if($prod->daily_usage_limit)
                                            <div style="font-weight: 600; color: #059669;">Limit: ₹{{ number_format($prod->daily_usage_limit) }}</div>
                                            <div style="font-size: 11px; color: #64748b;">EMI: ₹{{ number_format($prod->daily_repayment_amount) }}</div>
                                        @else
                                            <span style="color: #94a3b8; font-size: 12px;">Standard</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        @if($prod->is_active)
                                            <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 600;">Active</span>
                                        @else
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px;">Disabled</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <button type="button" class="btn btn-sm edit-prod-btn" 
                                            style="background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 600; font-size: 11px;"
                                            data-id="{{ $prod->id }}"
                                            data-name="{{ $prod->name }}"
                                            data-code="{{ $prod->code }}"
                                            data-category="{{ $prod->category }}"
                                            data-min-amount="{{ $prod->min_amount }}"
                                            data-max-amount="{{ $prod->max_amount }}"
                                            data-min-tenure="{{ $prod->min_tenure_months }}"
                                            data-max-tenure="{{ $prod->max_tenure_months }}"
                                            data-interest="{{ $prod->interest_rate_p_a }}"
                                            data-is-interest-free="{{ $prod->is_interest_free ? '1' : '0' }}"
                                            data-fee-type="{{ $prod->processing_fee_type }}"
                                            data-fee-value="{{ $prod->processing_fee_value }}"
                                            data-fee-slabs="{{ json_encode($prod->processing_fee_slabs) }}"
                                            data-daily-emi="{{ $prod->daily_repayment_amount }}"
                                            data-daily-limit="{{ $prod->daily_usage_limit }}"
                                            data-is-active="{{ $prod->is_active ? '1' : '0' }}"
                                            data-sort-order="{{ $prod->sort_order }}">
                                            Edit Policy
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No loan products configured.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Policy & Fee Management Form -->
            <div class="col-lg-4">
                <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0; background: #0f172a; border-radius: 8px 8px 0 0;">
                        <h5 class="mb-0" id="formTitle" style="font-size: 15px; font-weight: 700; color: #ffffff;">Configure Product &amp; Fee</h5>
                        <div style="font-size: 11px; color: #94a3b8;">Set pricing policies and processing fees</div>
                    </div>

                    <div class="p-3">
                        <form id="productForm" method="POST" action="{{ route('admin.finance.products.save') }}">
                            @csrf
                            <input type="hidden" id="prodId" name="id" value="">

                            <div class="mb-3">
                                <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Product Name *</label>
                                <input type="text" name="name" id="prodName" class="form-control" required style="font-size: 13px;" placeholder="e.g. Zero-CIBIL Daily Recovery">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Product Code *</label>
                                    <input type="text" name="code" id="prodCode" class="form-control" required style="font-size: 13px;" placeholder="e.g. zero_cibil_daily">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Category *</label>
                                    <select name="category" id="prodCategory" class="form-control" style="font-size: 13px;">
                                        <option value="cash_loan">Cash Loan</option>
                                        <option value="business_loan">Business Loan</option>
                                        <option value="zero_cibil">Zero-CIBIL Daily</option>
                                        <option value="virtual_loan">Virtual Loan</option>
                                        <option value="student_credit">Student Credit</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Processing Fee Control Block -->
                            <div class="p-3 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">
                                <div style="font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 8px;">
                                    ⚙️ Processing Fee Governance
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label" style="font-size: 11px; font-weight: 600; color: #334155;">Fee Type</label>
                                        <select name="processing_fee_type" id="prodFeeType" class="form-control" style="font-size: 12px;">
                                            <option value="fixed">Fixed Amount (₹)</option>
                                            <option value="percentage">Percentage (%)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size: 11px; font-weight: 600; color: #334155;">Fee Value *</label>
                                        <input type="number" step="0.01" name="processing_fee_value" id="prodFeeValue" class="form-control" required style="font-size: 12px;" placeholder="e.g. 2000.00">
                                    </div>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label" style="font-size: 11px; font-weight: 600; color: #334155;">
                                        Tiered Slabs JSON <small class="text-muted">(Optional for Virtual Loan / Slabs)</small>
                                    </label>
                                    <textarea name="processing_fee_slabs" id="prodFeeSlabs" rows="2" class="form-control" style="font-size: 11px; font-family: monospace;" placeholder='[{"amount":15000,"fee":2000},{"amount":20000,"fee":3000}]'></textarea>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Min Amount (₹) *</label>
                                    <input type="number" name="min_amount" id="prodMinAmount" class="form-control" required style="font-size: 13px;" placeholder="5000">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Max Amount (₹) *</label>
                                    <input type="number" name="max_amount" id="prodMaxAmount" class="form-control" required style="font-size: 13px;" placeholder="500000">
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Interest Rate (% p.a.)</label>
                                    <input type="number" step="0.01" name="interest_rate_p_a" id="prodInterest" class="form-control" required style="font-size: 13px;" placeholder="0.00">
                                </div>
                                <div class="col-6 d-flex align-items-center pt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_interest_free" id="prodIsInterestFree" value="1">
                                        <label class="form-check-label" for="prodIsInterestFree" style="font-size: 12px; font-weight: 600; color: #065f46;">
                                            Interest-Free (0%)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Daily EMI Recovery (₹)</label>
                                    <input type="number" step="0.01" name="daily_repayment_amount" id="prodDailyEmi" class="form-control" style="font-size: 13px;" placeholder="1000">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Daily Usage Cap (₹)</label>
                                    <input type="number" step="0.01" name="daily_usage_limit" id="prodDailyLimit" class="form-control" style="font-size: 13px;" placeholder="5000">
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Min Tenure (Mo)</label>
                                    <input type="number" name="min_tenure_months" id="prodMinTenure" class="form-control" style="font-size: 13px;" value="1">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Max Tenure (Mo)</label>
                                    <input type="number" name="max_tenure_months" id="prodMaxTenure" class="form-control" style="font-size: 13px;" value="60">
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-4 pt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="prodIsActive" value="1" checked>
                                    <label class="form-check-label" for="prodIsActive" style="font-size: 12px; font-weight: 600; color: #334155;">
                                        Active for Applicants
                                    </label>
                                </div>
                                <button type="button" id="resetBtn" class="btn btn-sm btn-link text-muted" style="font-size: 11px; text-decoration: none;">
                                    Reset Form
                                </button>
                            </div>

                            <button type="submit" class="btn w-100" style="background: #0f172a; color: #ffffff; font-weight: 700; font-size: 13px; padding: 10px; border-radius: 6px;">
                                Save Product &amp; Fee Policy
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('productForm');
    const formTitle = document.getElementById('formTitle');
    const resetBtn = document.getElementById('resetBtn');

    document.querySelectorAll('.edit-prod-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            form.action = '{{ url("admin/finance/products/save") }}/' + id;
            formTitle.textContent = 'Edit Product: ' + this.dataset.name;

            document.getElementById('prodId').value = id;
            document.getElementById('prodName').value = this.dataset.name;
            document.getElementById('prodCode').value = this.dataset.code;
            document.getElementById('prodCategory').value = this.dataset.category;
            document.getElementById('prodMinAmount').value = this.dataset.minAmount;
            document.getElementById('prodMaxAmount').value = this.dataset.maxAmount;
            document.getElementById('prodMinTenure').value = this.dataset.minTenure;
            document.getElementById('prodMaxTenure').value = this.dataset.maxTenure;
            document.getElementById('prodInterest').value = this.dataset.interest;
            document.getElementById('prodIsInterestFree').checked = (this.dataset.isInterestFree === '1');
            document.getElementById('prodFeeType').value = this.dataset.feeType;
            document.getElementById('prodFeeValue').value = this.dataset.feeValue;
            
            let slabs = this.dataset.feeSlabs;
            try {
                if (slabs && slabs !== 'null' && slabs !== '[]') {
                    document.getElementById('prodFeeSlabs').value = JSON.stringify(JSON.parse(slabs), null, 2);
                } else {
                    document.getElementById('prodFeeSlabs').value = '';
                }
            } catch(e) {
                document.getElementById('prodFeeSlabs').value = slabs || '';
            }

            document.getElementById('prodDailyEmi').value = this.dataset.dailyEmi || '';
            document.getElementById('prodDailyLimit').value = this.dataset.dailyLimit || '';
            document.getElementById('prodIsActive').checked = (this.dataset.isActive === '1');

            window.scrollTo({ top: form.offsetTop - 50, behavior: 'smooth' });
        });
    });

    resetBtn.addEventListener('click', function () {
        form.reset();
        form.action = '{{ route("admin.finance.products.save") }}';
        formTitle.textContent = 'Configure Product & Fee';
        document.getElementById('prodId').value = '';
    });
});
</script>
@endsection
