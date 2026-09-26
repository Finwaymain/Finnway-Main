<?php

use App\Http\Controllers\API\v1\Finance\FinanceApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/finance'], function () {
    Route::get('context', [FinanceApiController::class, 'getContext']);
    Route::get('products', [FinanceApiController::class, 'getProducts']);
    Route::post('calculate-eligibility', [FinanceApiController::class, 'calculateEligibility']);
    Route::post('vault/upload', [FinanceApiController::class, 'uploadVaultDocument']);
    Route::post('applications/initiate', [FinanceApiController::class, 'initiateApplication']);
    Route::post('applications/{id}/confirm-fee', [FinanceApiController::class, 'confirmFeePayment']);
    Route::get('lender-partners', [FinanceApiController::class, 'getLenderPartners']);
    Route::post('applications/{id}/select-partner', [FinanceApiController::class, 'selectPartner']);
    Route::post('applications/{id}/upload-proof', [FinanceApiController::class, 'submitProof']);
    Route::post('applications/{id}/upload-selfie', [FinanceApiController::class, 'submitAgentSelfie']);
    Route::post('applications/{id}/disbursement-account', [FinanceApiController::class, 'submitDisbursementAccount']);
    Route::get('dashboard', [FinanceApiController::class, 'getDashboard']);
    Route::post('daily-repayment', [FinanceApiController::class, 'repayDailyEmi']);
});
