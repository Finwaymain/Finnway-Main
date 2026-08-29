@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">🎁 Cashback & Discount Cost</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Cashback & Discount Cost</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Promotional Cost Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-light text-center">
                    <small class="text-muted font-weight-bold uppercase">Cashback Given</small>
                    <h3 class="font-weight-bold mt-1 mb-0 text-danger">₹{{ number_format($promoSummary['cashback_given']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-light text-center">
                    <small class="text-muted font-weight-bold uppercase">Discounts Given</small>
                    <h3 class="font-weight-bold mt-1 mb-0 text-warning">₹{{ number_format($promoSummary['discounts_given']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-light text-center">
                    <small class="text-muted font-weight-bold uppercase">Premium Discounts</small>
                    <h3 class="font-weight-bold mt-1 mb-0 text-info">₹{{ number_format($promoSummary['premium_discounts']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-danger text-white text-center">
                    <small class="text-white-50 font-weight-bold uppercase">Total Promotional Cost</small>
                    <h3 class="font-weight-bold mt-1 mb-0">₹{{ number_format($promoSummary['total_promotional_cost']) }}</h3>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Promotional Cost Breakdown & Budget Allocation</h4>
                <div class="row text-center mt-3">
                    <div class="col-md-3 border-right">
                        <p class="text-muted mb-1 font-weight-bold">Cashback Expense Ratio</p>
                        <h3 class="text-dark font-weight-bold">21.8%</h3>
                    </div>
                    <div class="col-md-3 border-right">
                        <p class="text-muted mb-1 font-weight-bold">Coupon Discounts</p>
                        <h3 class="text-dark font-weight-bold">33.0%</h3>
                    </div>
                    <div class="col-md-3 border-right">
                        <p class="text-muted mb-1 font-weight-bold">Premium Pass Benefits</p>
                        <h3 class="text-dark font-weight-bold">12.3%</h3>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 font-weight-bold">Referral Cash Rewards</p>
                        <h3 class="text-dark font-weight-bold">32.9%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
