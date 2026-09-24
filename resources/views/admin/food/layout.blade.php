@extends("layouts.app")

@section("style")
<style>
    /* Professional Enterprise Styling for Food & Restaurant Admin */
    .food-container {
        color: #111827 !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
    }
    
    .food-container .card {
        background-color: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    .food-container .card-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e5e7eb !important;
        color: #111827 !important;
        font-weight: 600 !important;
    }

    .food-container h1, 
    .food-container h2, 
    .food-container h3, 
    .food-container h4, 
    .food-container h5, 
    .food-container h6 {
        color: #111827 !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
    }

    .food-container p, 
    .food-container span, 
    .food-container td, 
    .food-container th, 
    .food-container div {
        color: #111827;
    }

    .food-container .text-muted {
        color: #4b5563 !important; /* High-contrast readable slate gray, never washed out */
    }

    .food-container label {
        color: #111827 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }

    /* High-contrast Tables */
    .food-container .table {
        background-color: #ffffff !important;
        color: #111827 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .food-container .table thead th {
        background-color: #f9fafb !important;
        color: #374151 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-top: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 12px 16px !important;
    }

    .food-container .table td {
        color: #111827 !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 16px !important;
        vertical-align: middle !important;
    }

    .food-container .table-hover tbody tr:hover {
        background-color: #f9fafb !important;
    }

    /* Clean Input Controls */
    .food-container .form-control {
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        color: #111827 !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        padding: 8px 12px !important;
    }

    .food-container .form-control:focus {
        border-color: #111827 !important;
        box-shadow: 0 0 0 1px #111827 !important;
        color: #111827 !important;
    }

    .food-container .input-group-text {
        background-color: #f9fafb !important;
        border: 1px solid #d1d5db !important;
        color: #374151 !important;
        font-weight: 600 !important;
    }

    /* Minimalist Professional Buttons */
    .food-container .btn {
        font-weight: 600 !important;
        border-radius: 6px !important;
        padding: 7px 16px !important;
        font-size: 13px !important;
        transition: all 0.15s ease-in-out !important;
    }

    .food-container .btn-primary {
        background-color: #111827 !important;
        border-color: #111827 !important;
        color: #ffffff !important;
    }

    .food-container .btn-primary:hover {
        background-color: #000000 !important;
        border-color: #000000 !important;
    }

    .food-container .btn-outline-secondary,
    .food-container .btn-outline-dark {
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        color: #111827 !important;
    }

    .food-container .btn-outline-secondary:hover,
    .food-container .btn-outline-dark:hover {
        background-color: #f9fafb !important;
        border-color: #9ca3af !important;
        color: #000000 !important;
    }

    /* Professional Metric Cards */
    .food-container .metric-card {
        background-color: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        padding: 16px 20px !important;
        height: 100% !important;
    }

    .food-container .metric-title {
        color: #4b5563 !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
    }

    .food-container .metric-value {
        color: #111827 !important;
        font-size: 26px !important;
        font-weight: 700 !important;
        line-height: 1.15 !important;
    }

    /* Badges */
    .food-container .badge {
        font-weight: 600 !important;
        padding: 5px 8px !important;
        border-radius: 4px !important;
        font-size: 12px !important;
    }

    .food-container .badge-light {
        background-color: #f3f4f6 !important;
        border: 1px solid #e5e7eb !important;
        color: #111827 !important;
    }

    .food-container .badge-success {
        background-color: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        color: #065f46 !important;
    }

    .food-container .badge-warning {
        background-color: #fffbeb !important;
        border: 1px solid #fde68a !important;
        color: #92400e !important;
    }

    .food-container .badge-danger {
        background-color: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #991b1b !important;
    }

    .food-container .badge-info {
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #1e40af !important;
    }
</style>
@endsection

@section("content")
<div class="container-fluid px-4 px-lg-5 py-4 food-container">

    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="background-color: #ecfdf5; border-left: 4px solid #10b981 !important; color: #065f46;">
            <strong>Success:</strong> {{ session("success") }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="background-color: #fef2f2; border-left: 4px solid #ef4444 !important; color: #991b1b;">
            <strong>Error:</strong> {{ session("error") }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @yield("food")
</div>
@endsection
