<?php

use App\Http\Controllers\API\v1\Finance\FinanceApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/finance'], function () {
    // ── Context & Products ──────────────────────────────────────────
    Route::get('context', [FinanceApiController::class, 'getContext']);
    Route::get('products', [FinanceApiController::class, 'getProducts']);
    Route::post('calculate-eligibility', [FinanceApiController::class, 'calculateEligibility']);

    // ── Document Vault ───────────────────────────────────────────────
    Route::post('vault/upload', [FinanceApiController::class, 'uploadVaultDocument']);

    // ── Application Lifecycle ─────────────────────────────────────────
    Route::post('applications/initiate', [FinanceApiController::class, 'initiateApplication']);
    Route::post('applications/{id}/save-applicant-details', [FinanceApiController::class, 'saveApplicantDetails']);
    Route::post('applications/{id}/save-business-details', [FinanceApiController::class, 'saveBusinessDetails']);
    Route::post('applications/{id}/select-tenure', [FinanceApiController::class, 'selectTenure']);
    Route::post('applications/{id}/confirm-fee', [FinanceApiController::class, 'confirmFeePayment']);
    Route::post('applications/{id}/additional-docs', [FinanceApiController::class, 'submitAdditionalDocs']);
    Route::get('applications/{id}/status', [FinanceApiController::class, 'getApplicationStatus']);

    // ── Lender Partner (Flow A — External Bank) ───────────────────────
    Route::get('lender-partners', [FinanceApiController::class, 'getLenderPartners']);
    Route::post('applications/{id}/verify-partner', [FinanceApiController::class, 'verifyPartner']);
    Route::post('applications/{id}/select-partner', [FinanceApiController::class, 'selectPartner']);
    Route::post('applications/{id}/upload-proof', [FinanceApiController::class, 'submitProof']);
    Route::post('applications/{id}/upload-selfie', [FinanceApiController::class, 'submitAgentSelfie']);
    Route::post('applications/{id}/disbursement-account', [FinanceApiController::class, 'submitDisbursementAccount']);

    // ── Wallet (Flow B — Internal Credit) ────────────────────────────
    Route::post('applications/{id}/activate-wallet', [FinanceApiController::class, 'activateWallet']);
    Route::get('wallet/{wallet_id}/status', [FinanceApiController::class, 'getWalletStatus']);
    Route::post('wallet/{wallet_id}/qr-payment', [FinanceApiController::class, 'processQrPayment']);

    // ── Dashboard & Repayment ─────────────────────────────────────────
    Route::get('dashboard', [FinanceApiController::class, 'getDashboard']);
    Route::post('daily-repayment', [FinanceApiController::class, 'repayDailyEmi']);
});
