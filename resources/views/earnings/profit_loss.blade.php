@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">📈 Profit & Loss Statement</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Profit & Loss</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                    <div>
                        <h4 class="card-title font-weight-bold mb-0">Financial Profit & Loss (P&L) Statement</h4>
                        <p class="text-muted small mb-0">Consolidated accounting statement of company revenues vs operational expenses.</p>
                    </div>
                    <span class="badge badge-success p-2 font-weight-bold" style="font-size: 1rem;">
                        Net Profit Margin: {{ $plStatement['profit_margin'] }}
                    </span>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <tbody>
                            <!-- Revenue Row -->
                            <tr class="table-success">
                                <td class="font-weight-bold" style="font-size: 1.1rem;">Gross Revenue (All Services & Subscriptions)</td>
                                <td class="text-right font-weight-bold text-success" style="font-size: 1.2rem;">
                                    + ₹{{ number_format($plStatement['gross_revenue']) }}
                                </td>
                            </tr>

                            <!-- Expense Rows -->
                            <tr>
                                <td class="pl-4">− Provider & Driver Payouts</td>
                                <td class="text-right text-danger">− ₹{{ number_format($plStatement['provider_payout']) }}</td>
                            </tr>
                            <tr>
                                <td class="pl-4">− Payment Gateway Charges (2%)</td>
                                <td class="text-right text-danger">− ₹{{ number_format($plStatement['gateway_charges']) }}</td>
                            </tr>
                            <tr>
                                <td class="pl-4">− Customer Cashback Given</td>
                                <td class="text-right text-danger">− ₹{{ number_format($plStatement['cashback']) }}</td>
                            </tr>
                            <tr>
                                <td class="pl-4">− Referral Cash Rewards Distributed</td>
                                <td class="text-right text-danger">− ₹{{ number_format($plStatement['referral_rewards']) }}</td>
                            </tr>
                            <tr>
                                <td class="pl-4">− Refunds & Cancelled Transactions</td>
                                <td class="text-right text-danger">− ₹{{ number_format($plStatement['refunds']) }}</td>
                            </tr>
                            <tr>
                                <td class="pl-4">− Other Platform Operational Expenses</td>
                                <td class="text-right text-danger">− ₹{{ number_format($plStatement['other_expenses']) }}</td>
                            </tr>

                            <!-- Net Profit Result Row -->
                            <tr class="table-primary">
                                <td class="font-weight-bold" style="font-size: 1.2rem;">= NET PROFIT (Company Net Earnings)</td>
                                <td class="text-right font-weight-bold text-primary" style="font-size: 1.3rem;">
                                    ₹{{ number_format($plStatement['net_profit']) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
