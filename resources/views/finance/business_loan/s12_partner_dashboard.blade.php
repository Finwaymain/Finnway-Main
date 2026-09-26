@extends('finance.layouts.base')
@section('title', 'Partners — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s11_app_generated', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Loan Partners</h2>
    
    @php
        $partners = $ctx['partners'] ?? [
            ['name' => 'Bajaj Finserv', 'range' => 'Up to 50L', 'rate' => '14%', 'fee' => '2%', 'locked' => false],
            ['name' => 'HDFC Bank', 'range' => 'Up to 2Cr', 'rate' => '12.5%', 'fee' => '1.5%', 'locked' => true],
            ['name' => 'IndusInd Bank', 'range' => 'Up to 1Cr', 'rate' => '15%', 'fee' => '2%', 'locked' => true]
        ];
    @endphp
    
    @foreach($partners as $p)
    <div style="border:1px solid #eee; padding:15px; border-radius:8px; margin-bottom:15px; position:relative;">
        @if($p['locked'])
            <div style="position:absolute; top:10px; right:10px; background:#ddd; padding:2px 8px; border-radius:10px; font-size:12px;">🔒 Locked</div>
        @endif
        <h3 style="margin:0 0 10px 0;">{{ $p['name'] }}</h3>
        <p><strong>Range:</strong> {{ $p['range'] }} | <strong>Rate:</strong> {{ $p['rate'] }}</p>
        <p><strong>Processing Fee:</strong> {{ $p['fee'] }}</p>
        
        @if(!$p['locked'])
            <a href="{{ route('finance.business_loan.s13_partner_select', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-outline" style="margin-top:10px; display:inline-block; padding:5px 15px;">Proceed</a>
        @endif
    </div>
    @endforeach
</div>
@endsection
