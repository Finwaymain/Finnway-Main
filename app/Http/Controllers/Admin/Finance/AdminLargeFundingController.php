<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\LargeFundingApplication;
use App\Models\Finance\LargeFundingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminLargeFundingController extends Controller
{
    /**
     * Pipeline List View (Zero Modals)
     */
    public function index(Request $request)
    {
        $query = LargeFundingApplication::with(['customer', 'documents'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'LIKE', "%{$search}%")
                  ->orWhere('applicant_name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile_number', 'LIKE', "%{$search}%")
                  ->orWhere('business_name', 'LIKE', "%{$search}%")
                  ->orWhere('pan_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('stage')) {
            $query->where('current_stage', $request->stage);
        }

        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }

        if ($request->filled('fee_status')) {
            $query->where('fee_payment_status', $request->fee_status);
        }

        $applications = $query->paginate(20)->withQueryString();

        // Metrics
        $totalCount     = LargeFundingApplication::count();
        $underReview    = LargeFundingApplication::whereIn('current_stage', [1, 2])->count();
        $feePending     = LargeFundingApplication::where('current_stage', 3)->where('fee_payment_status', 'pending')->count();
        $inSyndicate    = LargeFundingApplication::whereIn('current_stage', [4, 5, 6])->count();
        $approvedCount  = LargeFundingApplication::where('current_stage', '>=', 7)->count();
        $approvedAmount = LargeFundingApplication::where('current_stage', '>=', 7)->sum('approved_amount') ?: 0;

        return view('admin.finance.large_funding.index', compact(
            'applications', 'totalCount', 'underReview', 'feePending', 'inSyndicate', 'approvedCount', 'approvedAmount'
        ));
    }

    /**
     * Detailed Application Inspector & Action Center (Zero Modals)
     */
    public function show($id)
    {
        $application = LargeFundingApplication::with(['customer', 'documents'])->findOrFail($id);
        return view('admin.finance.large_funding.show', compact('application'));
    }

    /**
     * Action 1: Document Review (Inline Card Action)
     */
    public function reviewDocument(Request $request, $id)
    {
        $doc = LargeFundingDocument::findOrFail($id);
        $request->validate([
            'status'         => 'required|in:verified,rejected',
            'admin_feedback' => 'nullable|string|max:500',
        ]);

        $doc->update([
            'status'         => $request->status,
            'admin_feedback' => $request->admin_feedback,
            'reviewed_at'    => now(),
            'reviewed_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Document status updated to ' . strtoupper($request->status));
    }

    /**
     * Action 2: Verify All Documents & Project (Inline Card Action)
     */
    public function verifyAllDocuments(Request $request, $id)
    {
        $application = LargeFundingApplication::findOrFail($id);
        
        // Mark all docs as verified
        $application->documents()->update([
            'status'      => 'verified',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Advance to Stage 2 / 3
        $application->update([
            'current_stage'      => 2,
            'application_status' => 'DOCUMENTS_VERIFIED',
            'admin_notes'        => $request->input('admin_notes', 'Documents and project pitch verified by Underwriting Committee.')
        ]);

        return back()->with('success', 'All documents and business project marked as VERIFIED! Ready for Fee Adjustment.');
    }

    /**
     * Action 3: Adjust / Set Service Fee (Inline Card Action)
     */
    public function updateFee(Request $request, $id)
    {
        $application = LargeFundingApplication::findOrFail($id);

        $request->validate([
            'service_fee_base'   => 'required|numeric|min:0',
            'fee_payment_status' => 'required|in:pending,paid,waived',
            'fee_deadline'       => 'nullable|date',
        ]);

        $base = (float)$request->service_fee_base;
        $gst  = round($base * 0.18, 2);
        $total = $base + $gst;

        $updateData = [
            'service_fee_base'   => $base,
            'service_fee_gst'    => $gst,
            'service_fee_total'  => $total,
            'fee_payment_status' => $request->fee_payment_status,
            'fee_deadline'       => $request->fee_deadline ? date('Y-m-d H:i:s', strtotime($request->fee_deadline)) : null,
        ];

        if ($request->fee_payment_status === 'paid') {
            $updateData['fee_paid_at'] = now();
            $updateData['fee_payment_ref'] = $request->input('remarks', 'Admin Marked as Paid');
            // Advance to stage 4 if currently at 3 or lower
            if ($application->current_stage <= 3) {
                $updateData['current_stage'] = 4;
                $updateData['application_status'] = 'FORWARDED_TO_INVESTORS';
            }
        } elseif ($request->fee_payment_status === 'waived') {
            $updateData['service_fee_total'] = 0;
            if ($application->current_stage <= 3) {
                $updateData['current_stage'] = 4;
                $updateData['application_status'] = 'FORWARDED_TO_INVESTORS';
            }
        } else {
            // Pending payment
            $updateData['current_stage'] = 3;
            $updateData['application_status'] = 'FEE_PENDING';
        }

        $application->update($updateData);

        return back()->with('success', 'Service Fee updated: Base ₹' . number_format($base) . ' + GST ₹' . number_format($gst) . ' = Total ₹' . number_format($total));
    }

    /**
     * Action 4: Update Pipeline Stage (Milestones 1 to 8) (Inline Card Action)
     */
    public function updateStage(Request $request, $id)
    {
        $application = LargeFundingApplication::findOrFail($id);

        $request->validate([
            'stage'                  => 'required|integer|between:1,8',
            'status'                 => 'required|string',
            'approved_amount'        => 'nullable|numeric|min:0',
            'approved_investor_name' => 'nullable|string|max:150',
            'disbursement_utr'       => 'nullable|string|max:100',
            'admin_notes'            => 'nullable|string',
            'rejection_reason'       => 'nullable|string',
        ]);

        $stage = (int)$request->stage;
        $status = $request->status;

        $updateData = [
            'current_stage'      => $stage,
            'application_status' => $status,
            'admin_notes'        => $request->admin_notes,
        ];

        // Specific Stage Handling
        if ($stage === 7 || $status === 'FUND_APPROVED') {
            $updateData['approved_amount']        = $request->approved_amount ?: 100000000.00; // Default ₹10 Cr
            $updateData['approved_investor_name'] = $request->approved_investor_name ?: 'Fiinway Capital Partners & Syndicate';
        }

        if ($stage === 8 || $status === 'DISBURSED') {
            $updateData['disbursement_utr'] = $request->disbursement_utr ?: 'UTR' . strtoupper(substr(md5(time()), 0, 10));
            $updateData['disbursed_at']     = now();
        }

        if ($status === 'REJECTED') {
            $updateData['rejection_reason'] = $request->rejection_reason ?: 'Application not meeting current investor syndicate underwriting criteria.';
        }

        $application->update($updateData);

        return back()->with('success', 'Pipeline Stage progressed to: Stage ' . $stage . ' (' . $application->stage_label . ')');
    }
}
