<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LanguageController;
use App\Helpers\OnboardingAccess;



/*

|--------------------------------------------------------------------------

| Web Routes

|--------------------------------------------------------------------------

|

| Here is where you can register web routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| contains the "web" middleware group. Now create something great!

|

*/

// clear cache
Route::get('/clear', function() {
   Artisan::call('cache:clear');
   Artisan::call('config:clear');
   Artisan::call('config:cache');
   Artisan::call('view:clear');
   Artisan::call('optimize:clear');
   return "Cleared!";
});


Auth::routes();

// Serve Marketplace Product Images dynamically from storage or public
Route::get('/assets/images/marketplace/{filename}', [App\Http\Controllers\API\v1\ProductController::class, 'serveMarketplaceImage'])->where('filename', '.*');
Route::get('/storage/marketplace/{filename}', [App\Http\Controllers\API\v1\ProductController::class, 'serveMarketplaceImage'])->where('filename', '.*');
Route::get('/storage/app/public/marketplace/{filename}', [App\Http\Controllers\API\v1\ProductController::class, 'serveMarketplaceImage'])->where('filename', '.*');

Route::get('/onboarding/marketplace.html', function () {
    return response()->file(public_path('onboarding-assets/marketplace.html'));
});

Route::get('/onboarding/food.html', function () {
    if (file_exists(public_path('onboarding-assets/food.html'))) {
        return response()->file(public_path('onboarding-assets/food.html'));
    }
    if (file_exists(public_path('onboarding-assets/food/index.html'))) {
        return response()->file(public_path('onboarding-assets/food/index.html'));
    }
    return OnboardingAccess::renderView('food');
});

Route::get('/food', function () {
    if (file_exists(public_path('onboarding-assets/food.html'))) {
        return response()->file(public_path('onboarding-assets/food.html'));
    }
    if (file_exists(public_path('onboarding-assets/food/index.html'))) {
        return response()->file(public_path('onboarding-assets/food/index.html'));
    }
    return OnboardingAccess::renderView('food');
});

Route::get('/onboarding', function (\Illuminate\Http\Request $request) {
    if (!$request->has('driver_id') && !$request->has('accesstoken') && !$request->has('mode') && !$request->has('step')) {
        if (file_exists(public_path('onboarding-assets/welcome.html'))) {
            return response()->file(public_path('onboarding-assets/welcome.html'));
        }
        if (view()->exists('welcome')) {
            return OnboardingAccess::renderView('welcome');
        }
    }
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    return OnboardingAccess::renderView('onboarding');
});

Route::get('/onboarding/welcome', function (\Illuminate\Http\Request $request) {
    if (file_exists(public_path('onboarding-assets/welcome.html'))) {
        return response()->file(public_path('onboarding-assets/welcome.html'));
    }
    return OnboardingAccess::renderView('welcome');
});

Route::get('/onboarding/welcome.html', function () {
    if (file_exists(public_path('onboarding-assets/welcome.html'))) {
        return response()->file(public_path('onboarding-assets/welcome.html'));
    }
    return OnboardingAccess::renderView('welcome');
});

Route::get('/welcome', function (\Illuminate\Http\Request $request) {
    if (file_exists(public_path('onboarding-assets/welcome.html'))) {
        return response()->file(public_path('onboarding-assets/welcome.html'));
    }
    return OnboardingAccess::renderView('welcome');
});

Route::get('/welcome.html', function () {
    if (file_exists(public_path('onboarding-assets/welcome.html'))) {
        return response()->file(public_path('onboarding-assets/welcome.html'));
    }
    return OnboardingAccess::renderView('welcome');
});

Route::get('/onboarding/join-fiinway', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('join-fiinway');
});

Route::get('/onboarding/more', function (\Illuminate\Http\Request $request) {
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    return OnboardingAccess::renderView('more');
});

Route::get('/onboarding/dashboard', function (\Illuminate\Http\Request $request) {
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    if (view()->exists('onboarding-dashboard')) {
        return OnboardingAccess::renderView('onboarding-dashboard');
    }
    return OnboardingAccess::renderView('dashboard');
});

Route::get('/onboarding/smartvalue', function (\Illuminate\Http\Request $request) {
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    return OnboardingAccess::renderView('smartvalue');
});

Route::get('/onboarding/medical-cashback', function (\Illuminate\Http\Request $request) {
    if (file_exists(public_path('onboarding-assets/medical-cashback.html'))) {
        return response()->file(public_path('onboarding-assets/medical-cashback.html'));
    }
    return OnboardingAccess::renderView('medical-cashback');
});

Route::get('/onboarding/medical-cashback.html', function () {
    if (file_exists(public_path('onboarding-assets/medical-cashback.html'))) {
        return response()->file(public_path('onboarding-assets/medical-cashback.html'));
    }
    return OnboardingAccess::renderView('medical-cashback');
});

Route::get('/medical-cashback', function (\Illuminate\Http\Request $request) {
    if (file_exists(public_path('onboarding-assets/medical-cashback.html'))) {
        return response()->file(public_path('onboarding-assets/medical-cashback.html'));
    }
    return OnboardingAccess::renderView('medical-cashback');
});

Route::get('/medical-cashback.html', function () {
    if (file_exists(public_path('onboarding-assets/medical-cashback.html'))) {
        return response()->file(public_path('onboarding-assets/medical-cashback.html'));
    }
    return OnboardingAccess::renderView('medical-cashback');
});

Route::get('/medical_cashback', function (\Illuminate\Http\Request $request) {
    if (file_exists(public_path('onboarding-assets/medical-cashback.html'))) {
        return response()->file(public_path('onboarding-assets/medical-cashback.html'));
    }
    return OnboardingAccess::renderView('medical-cashback');
});

Route::get('/onboarding/referral', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('referral');
});

Route::get('/onboarding/referral.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('referral');
});

Route::get('/referral', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('referral');
});

Route::get('/referral.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('referral');
});

Route::get('/onboarding/partner-referral', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('referral');
});

Route::get('/partner-referral', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('referral');
});

Route::match(['get', 'post'], '/api/v1/referral-dashboard-stats', [\App\Http\Controllers\API\v1\ReferralDashboardAPIController::class, 'getStats']);
Route::match(['get', 'post'], '/api/referral-dashboard-stats', [\App\Http\Controllers\API\v1\ReferralDashboardAPIController::class, 'getStats']);

Route::get('/ref/{code}', function ($code) {
    $cleanCode = strtoupper(trim($code));
    return view('referral-landing', ['referralCode' => $cleanCode]);
});

Route::get('/join/{code}', function ($code) {
    $cleanCode = strtoupper(trim($code));
    return view('referral-landing', ['referralCode' => $cleanCode]);
});

Route::get('/wallet', function (\Illuminate\Http\Request $request) {
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    return OnboardingAccess::renderView('wallet');
});

Route::get('/wallet.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('wallet');
});

Route::get('/onboarding/wallet', function (\Illuminate\Http\Request $request) {
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    return OnboardingAccess::renderView('wallet');
});

Route::get('/onboarding/wallet.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('wallet');
});

Route::get('/onboarding/smartvalue.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('smartvalue');
});

Route::get('/smartvalue', function (\Illuminate\Http\Request $request) {
    if (!OnboardingAccess::validate($request)) {
        return OnboardingAccess::unauthorizedResponse();
    }
    return OnboardingAccess::renderView('smartvalue');
});

Route::get('/smartvalue.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('smartvalue');
});

Route::get('/onboarding/marketplace.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('marketplace');
});

Route::get('/marketplace.html', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('marketplace');
});

Route::get('/marketplace', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('marketplace');
});

Route::get('/onboarding/marketplace', function (\Illuminate\Http\Request $request) {
    return OnboardingAccess::renderView('marketplace');
});

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::get('/updateDriverStatus/{id}', [App\Http\Controllers\HomeController::class, 'updateDriverStatus'])->name('updatestatus');

Route::get('home/sales_overview', [App\Http\Controllers\HomeController::class, 'getSalesOverview']);



Route::get('lang/change', [languageController::class, 'change'])->name('changeLang');

Route::get('/getlang', [languageController::class, 'getLangauage'])->name('language.header');

Route::post('/gecode/{slugid}', [languageController::class, 'getCode'])->name('lang.code');

Route::get('language', [languageController::class, 'index'])->name('language');

Route::get('/language/create', [languageController::class, 'create'])->name('language.create');

Route::post('/language/storeuser', [languageController::class, 'storeuser'])->name('language.storeuser');

Route::get('/language/edit/{id}', [languageController::class, 'edit'])->name('language.edit');

Route::put('language/update/{id}', [languageController::class, 'userUpdate'])->name('language.update');

Route::get('/language/delete/{id}', [languageController::class, 'deleteUser'])->name('language.delete');

Route::post('language/switch', [languageController::class, 'toggalSwitch'])->name('language.switch');
;





Route::post('payments/getpaytmchecksum', [App\Http\Controllers\PaymentController::class, 'getPaytmChecksum']);

Route::post('payments/validatechecksum', [App\Http\Controllers\PaymentController::class, 'validateChecksum']);

Route::post('payments/initiatepaytmpayment', [App\Http\Controllers\PaymentController::class, 'initiatePaytmPayment']);

Route::get('payments/paytmpaymentcallback', [App\Http\Controllers\PaymentController::class, 'paytmPaymentcallback']);

Route::post('payments/paypalclientid', [App\Http\Controllers\PaymentController::class, 'getPaypalClienttoken']);

Route::post('payments/paypaltransaction', [App\Http\Controllers\PaymentController::class, 'createBraintreePayment']);

Route::post('payments/stripepaymentintent', [App\Http\Controllers\PaymentController::class, 'createStripePaymentIntent']);




Route::post('payments/razorpay/createorder', [App\Http\Controllers\RazorPayController::class, 'createOrderid']);




################################# Earning Management Single Screen Routes ###########################
Route::get('/earnings', [App\Http\Controllers\EarningController::class, 'index'])->name('earnings.index');
Route::get('/earnings/api/stats', [App\Http\Controllers\EarningController::class, 'getApiStats'])->name('earnings.api.stats');
Route::get('/earnings/export', [App\Http\Controllers\EarningController::class, 'exportReport'])->name('earnings.export');
Route::get('/earnings/export/all', [App\Http\Controllers\EarningController::class, 'exportReport'])->name('earnings.export.all');
Route::get('/earnings/export/wallet', [App\Http\Controllers\EarningController::class, 'exportWallet'])->name('earnings.export.wallet');
Route::get('/earnings/export/company', [App\Http\Controllers\EarningController::class, 'exportCompany'])->name('earnings.export.company');
Route::get('/earnings/export/services', [App\Http\Controllers\EarningController::class, 'exportServices'])->name('earnings.export.services');
Route::get('/earnings/export/marketplace', [App\Http\Controllers\EarningController::class, 'exportMarketplace'])->name('earnings.export.marketplace');
Route::get('/earnings/export/premium', [App\Http\Controllers\EarningController::class, 'exportPremium'])->name('earnings.export.premium');
Route::get('/earnings/export/referral', [App\Http\Controllers\EarningController::class, 'exportReferral'])->name('earnings.export.referral');
Route::get('/earnings/export/payments', [App\Http\Controllers\EarningController::class, 'exportPayments'])->name('earnings.export.payments');
Route::get('/earnings/export/settlement', [App\Http\Controllers\EarningController::class, 'exportSettlement'])->name('earnings.export.settlement');
Route::get('/earnings/export/profit-loss', [App\Http\Controllers\EarningController::class, 'exportProfitLoss'])->name('earnings.export.profit-loss');
Route::get('/earnings/export/reports', [App\Http\Controllers\EarningController::class, 'exportDailyReports'])->name('earnings.export.reports');
Route::post('/earnings/reset-test-data', [App\Http\Controllers\EarningController::class, 'resetTestData'])->name('earnings.resetTestData');
Route::get('/earnings/reset-test-data', [App\Http\Controllers\EarningController::class, 'resetTestData']);
Route::post('/earnings/seed-test-data', [App\Http\Controllers\EarningController::class, 'seedSampleData'])->name('earnings.seedSampleData');
Route::get('/earnings/seed-test-data', [App\Http\Controllers\EarningController::class, 'seedSampleData']);


// Sub-routes redirection to master single-screen dashboard
Route::get('/earnings/revenue-dashboard', [App\Http\Controllers\EarningController::class, 'revenueDashboard'])->name('earnings.revenue-dashboard');
Route::get('/earnings/service-commission', [App\Http\Controllers\EarningController::class, 'serviceCommission'])->name('earnings.service-commission');
Route::get('/earnings/marketplace-commission', [App\Http\Controllers\EarningController::class, 'marketplaceCommission'])->name('earnings.marketplace-commission');
Route::get('/earnings/premium-plans', [App\Http\Controllers\EarningController::class, 'premiumPlans'])->name('earnings.premium-plans');
Route::get('/earnings/referral', [App\Http\Controllers\EarningController::class, 'referralCostRevenue'])->name('earnings.referral');
Route::get('/earnings/payment-transactions', [App\Http\Controllers\EarningController::class, 'paymentTransactions'])->name('earnings.payment-transactions');
Route::get('/earnings/promotions-discounts', [App\Http\Controllers\EarningController::class, 'cashbackDiscounts'])->name('earnings.promotions-discounts');
Route::get('/earnings/settlements-payouts', [App\Http\Controllers\EarningController::class, 'settlementsPayouts'])->name('earnings.settlements-payouts');
Route::get('/earnings/profit-loss', [App\Http\Controllers\EarningController::class, 'profitLoss'])->name('earnings.profit-loss');
Route::get('/earnings/reports', [App\Http\Controllers\EarningController::class, 'earningReports'])->name('earnings.reports');

################################# Sub-Admin Management Routes ###########################
Route::get('/sub-admins', [App\Http\Controllers\SubAdminController::class, 'index'])->name('sub-admins.index');
Route::get('/sub-admins/create', [App\Http\Controllers\SubAdminController::class, 'create'])->name('sub-admins.create');
Route::post('/sub-admins/store', [App\Http\Controllers\SubAdminController::class, 'store'])->name('sub-admins.store');
Route::get('/sub-admins/edit/{id}', [App\Http\Controllers\SubAdminController::class, 'edit'])->name('sub-admins.edit');
Route::put('/sub-admins/update/{id}', [App\Http\Controllers\SubAdminController::class, 'update'])->name('sub-admins.update');
Route::delete('/sub-admins/delete/{id}', [App\Http\Controllers\SubAdminController::class, 'destroy'])->name('sub-admins.destroy');
Route::get('/sub-admins/toggle-status/{id}', [App\Http\Controllers\SubAdminController::class, 'toggleStatus'])->name('sub-admins.toggle-status');

################################# Medical Cashback Admin Routes ###########################
Route::get('/admin/medical-cashback', [App\Http\Controllers\MedicalCashbackAdminController::class, 'index'])->name('admin.medical.index');
Route::get('/admin/medical-cashback/cards', [App\Http\Controllers\MedicalCashbackAdminController::class, 'cards'])->name('admin.medical.cards');
Route::get('/admin/medical-cashback/manage-plans', [App\Http\Controllers\MedicalCashbackAdminController::class, 'managePlans'])->name('admin.medical.plans.index');
Route::post('/admin/medical-cashback/plans/store', [App\Http\Controllers\MedicalCashbackAdminController::class, 'storePlan'])->name('admin.medical.plans.store');
Route::post('/admin/medical-cashback/plans/update/{id}', [App\Http\Controllers\MedicalCashbackAdminController::class, 'updatePlan'])->name('admin.medical.plans.update');
Route::get('/admin/medical-cashback/plans/toggle/{id}', [App\Http\Controllers\MedicalCashbackAdminController::class, 'togglePlanStatus'])->name('admin.medical.plans.toggle');
Route::get('/admin/medical-cashback/plans/delete/{id}', [App\Http\Controllers\MedicalCashbackAdminController::class, 'deletePlan'])->name('admin.medical.plans.delete');
Route::post('/admin/medical-cashback/approve/{id}', [App\Http\Controllers\MedicalCashbackAdminController::class, 'approve'])->name('admin.medical.approve');
Route::post('/admin/medical-cashback/reupload/{id}', [App\Http\Controllers\MedicalCashbackAdminController::class, 'reupload'])->name('admin.medical.reupload');
Route::post('/admin/medical-cashback/reject/{id}', [App\Http\Controllers\MedicalCashbackAdminController::class, 'reject'])->name('admin.medical.reject');

################################# Users Show And Schedule Start Here 2025-09-01 ###########################
Route::get('/users/all', [App\Http\Controllers\UserController::class, 'allUsersIndex'])->name('users.all');
Route::get('/users/kyc-verification', [App\Http\Controllers\UserController::class, 'kycVerificationIndex'])->name('users.kycVerification');
Route::post('/users/kyc-verification/update', [App\Http\Controllers\UserController::class, 'updateKycStatus'])->name('users.kycVerification.update');
Route::post('/users/kyc/update', [App\Http\Controllers\UserController::class, 'updateKycStatus'])->name('users.kyc.update');
Route::post('/users/quick-update', [App\Http\Controllers\UserController::class, 'quickUpdateUser'])->name('users.quickUpdate');
Route::post('/driver/quick-update', [App\Http\Controllers\DriverController::class, 'quickUpdateDriver'])->name('driver.quickUpdate');
Route::get('/users_shudule', [App\Http\Controllers\UserController::class, 'users_shudule_index'])->name('users_shudule'); 
Route::post('/switch_kyc_status_update/{tableName}', [App\Http\Controllers\UserController::class, 'switch_kyc_status_update']); // kyc status
Route::post('/update_user_wallet', [App\Http\Controllers\UserController::class, 'update_user_wallet']);



###### Admin Schedule here For Users ############
// all users wallet and earn wallet 
Route::post('/update_transfer_wallet_all', [App\Http\Controllers\UserController::class, 'update_transfer_wallet_all']);
// schedule 1  sender receiver
Route::post('/update_wallet_all', [App\Http\Controllers\UserController::class, 'update_wallet_all']);
// schedule 2  daily increment 
Route::post('/update_wallet_all2', [App\Http\Controllers\UserController::class, 'update_wallet_all2']);
// schedule 3 deduction 
Route::post('/update_wallet_all3', [App\Http\Controllers\UserController::class, 'update_wallet_all3']);
// schedule 4 refer and earn 
Route::post('/update_refer_earn', [App\Http\Controllers\UserController::class, 'update_refer_earn']);

###### Admin Schedule here For Users ############








Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users');

Route::get('/users/edit/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');

Route::get('/user/delete/{id}', [App\Http\Controllers\UserController::class, 'deleteUser'])->name('user.delete');

Route::put('user/update/{id}', [App\Http\Controllers\UserController::class, 'userUpdate'])->name('user.update');

Route::get('/users/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('users.profile');

Route::post('/users/profile/update/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('users.profile.update');

Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');

Route::post('/users/storeuser', [App\Http\Controllers\UserController::class, 'storeuser'])->name('users.storeuser');

Route::get('/users/show/{id}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');

Route::post('/users/add-wallet/{id}', [App\Http\Controllers\UserController::class, 'addWallet'])->name('user.wallet');

Route::get('/users/changeStatus/{id}', [App\Http\Controllers\UserController::class, 'changeStatus'])->name('users.changeStatus');

Route::post('/switch', [App\Http\Controllers\UserController::class, 'toggalSwitch']);



Route::get('/users_category', [App\Http\Controllers\UserCategoryController::class, 'index'])->name('users_category');

Route::get('/users_category/delete/{id}', [App\Http\Controllers\UserCategoryController::class, 'delete'])->name('userscategory.delete');

Route::put('users_category/update/{id}', [App\Http\Controllers\UserCategoryController::class, 'update'])->name('userscategory.update');

Route::post('/userscategory/store', [App\Http\Controllers\UserCategoryController::class, 'store'])->name('userscategory.store');



Route::get('/drivers', [App\Http\Controllers\DriverController::class, 'index'])->name('drivers');

Route::get('/drivers/approved', [App\Http\Controllers\DriverController::class, 'approvedDrivers'])->name('drivers.approved');

Route::get('/drivers/pending', [App\Http\Controllers\DriverController::class, 'pendingDrivers'])->name('drivers.pending');

Route::get('/drivers/edit/{id}', [App\Http\Controllers\DriverController::class, 'edit'])->name('drivers.edit');

Route::get('/drivers/documentstatus/{id}/{type}', [App\Http\Controllers\DriverController::class, 'statusAproval'])->name('drivers.documentstatus');

Route::get('/drivers/create', [App\Http\Controllers\DriverController::class, 'create'])->name('drivers.create');

Route::post('/drivers/store', [App\Http\Controllers\DriverController::class, 'store'])->name('drivers.store');

Route::get('/driver/delete/{id}', [App\Http\Controllers\DriverController::class, 'deleteDriver'])->name('driver.delete');

Route::put('driver/update/{id}', [App\Http\Controllers\DriverController::class, 'updateDriver'])->name('driver.update');

Route::get('/driver/show/{id}', [App\Http\Controllers\DriverController::class, 'show'])->name('driver.show');

Route::post('/driver/add-wallet/{id}', [App\Http\Controllers\DriverController::class, 'addWallet'])->name('driver.wallet');

Route::get('/driver/changeStatus/{id}', [App\Http\Controllers\DriverController::class, 'changeStatus'])->name('driver.changeStatus');

Route::get('/driver/verifyAndEnable/{id}', [App\Http\Controllers\DriverController::class, 'verifyAndEnable'])->name('driver.verifyAndEnable');

Route::get('/driver/document/view/{id}', [App\Http\Controllers\DriverController::class, 'documentView'])->name('driver.documentView');
Route::get('/driver/documents/approve-all/{id}', [App\Http\Controllers\DriverController::class, 'approveAllDocuments'])->name('driver.approveAllDocuments');

Route::get('/driver/uploaddocument/{id}/{doc_id}', [App\Http\Controllers\DriverController::class, 'uploaddocument'])->name('driver.uploaddocument');

Route::get('/driver/upload/document/{id}', [App\Http\Controllers\DriverController::class, 'uploaddocument'])->name('driver.upload_document');

Route::put('/driver/updatedocument/{id}', [App\Http\Controllers\DriverController::class, 'updatedocument'])->name('driver.updatedocument');

Route::post('/driver/model/{brandId}', [App\Http\Controllers\DriverController::class, 'getModel'])->name('driver.model');

Route::post('/driver/brand/{vehicleType_id}', [App\Http\Controllers\DriverController::class, 'getBrand'])->name('driver.brand');

Route::get('/driver/download', [App\Http\Controllers\DriverController::class, 'download'])->name('driver.download');

Route::get('status-update/{id}', [App\Http\Controllers\DriverController::class, 'statusupdate'])->name('status-update');

Route::post('driver/switch', [App\Http\Controllers\DriverController::class, 'toggalSwitch']);
Route::post('drivers/switch', [App\Http\Controllers\DriverController::class, 'toggalSwitch']);

// Driver Schedules and Wallet Actions
Route::post('/driver/update_user_wallet', [App\Http\Controllers\DriverController::class, 'update_driver_wallet']);
Route::post('/driver/update_transfer_wallet_all', [App\Http\Controllers\DriverController::class, 'update_transfer_wallet_all']);
Route::post('/driver/update_wallet_all', [App\Http\Controllers\DriverController::class, 'update_wallet_all']);
Route::post('/driver/update_wallet_all2', [App\Http\Controllers\DriverController::class, 'update_wallet_all2']);
Route::post('/driver/update_wallet_all3', [App\Http\Controllers\DriverController::class, 'update_wallet_all3']);
Route::post('/driver/update_refer_earn', [App\Http\Controllers\DriverController::class, 'update_refer_earn']);



Route::get('cms', [App\Http\Controllers\CmsController::class, 'index'])->name('cms');

Route::get('/cms/edit/{id}', [App\Http\Controllers\CmsController::class, 'edit'])->name('cms.edit');

Route::put('cms/updateCms/{id}', [App\Http\Controllers\CmsController::class, 'updateCms'])->name('cms.updateCms');

Route::get('/cms/create', [App\Http\Controllers\CmsController::class, 'create'])->name('cms.create');

Route::post('/cms/store', [App\Http\Controllers\CmsController::class, 'store'])->name('cms.store');

Route::get('/cms/destroycms/{id}', [App\Http\Controllers\CmsController::class, 'destroycms'])->name('cms.destroycms');

Route::get('/cms/changeStatus/{id}', [App\Http\Controllers\CmsController::class, 'changeStatus'])->name('cms.changeStatus');

Route::post('cms/switch', [App\Http\Controllers\CmsController::class, 'toggalSwitch']);



/*Route::get('/notification', [App\Http\Controllers\NotificationController::class, 'index'])->name('notification');

Route::get('/notification/delete/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('notification.delete');

Route::get('/notification/show/{id}', [App\Http\Controllers\NotificationController::class, 'show'])->name('notification.show');*/



Route::get('/notification', [App\Http\Controllers\AdminNotificationController::class, 'index'])->name('notifications');

Route::get('/notification/create', [App\Http\Controllers\AdminNotificationController::class, 'create'])->name('notifications.create');

Route::post('/notification/send', [App\Http\Controllers\AdminNotificationController::class, 'send'])->name('notifications.send');

Route::get('/notification/delete/{id}', [App\Http\Controllers\AdminNotificationController::class, 'delete'])->name('notifications.delete');





Route::get('/rides/all/{id?}', [App\Http\Controllers\RidesController::class, 'all'])->name('rides.all');

Route::get('/rides/new', [App\Http\Controllers\RidesController::class, 'new'])->name('rides.new');

Route::get('/rides/confirmed', [App\Http\Controllers\RidesController::class, 'confirmed'])->name('rides.confirmed');

Route::get('/rides/onRide', [App\Http\Controllers\RidesController::class, 'onRide'])->name('rides.onRide');

Route::get('/rides/rejected', [App\Http\Controllers\RidesController::class, 'rejected'])->name('rides.rejected');

Route::get('/rides/completed', [App\Http\Controllers\RidesController::class, 'completed'])->name('rides.completed');

Route::get('/ride/delete/{rideid}', [App\Http\Controllers\RidesController::class, 'deleteRide'])->name('ride.delete');

Route::get('/ride/show/{id}', [App\Http\Controllers\RidesController::class, 'show'])->name('ride.show');

Route::get('/rides/filter', [App\Http\Controllers\RidesController::class, 'filterRides'])->name('rides.filter');

Route::put('/rides/update/{id}', [App\Http\Controllers\RidesController::class, 'updateRide'])->name('rides.update');

Route::get('/reviews/{id}', [App\Http\Controllers\RidesController::class, 'index'])->name('restaurants.reviews');



// parcle orders routes

Route::get('/parcel/all/{id?}', [App\Http\Controllers\ParcelOrdersController::class, 'all'])->name('parcel.all');

Route::get('/parcel/delete/{rideid}', [App\Http\Controllers\ParcelOrdersController::class, 'deleteRide'])->name('parcel.delete');

Route::get('/parcel/show/{id}', [App\Http\Controllers\ParcelOrdersController::class, 'show'])->name('parcel.show');

Route::put('/parcel/update/{id}', [App\Http\Controllers\ParcelOrdersController::class, 'updateRide'])->name('parcel.update');

Route::get('/parcel/confirmed', [App\Http\Controllers\ParcelOrdersController::class, 'confirmed'])->name('parcel.confirmed');

Route::get('/parcel/rejected', [App\Http\Controllers\ParcelOrdersController::class, 'rejected'])->name('parcel.rejected');

Route::get('/parcel/completed', [App\Http\Controllers\ParcelOrdersController::class, 'completed'])->name('parcel.completed');



Route::get('/vehicle/index', [App\Http\Controllers\VehicleController::class, 'vehicleType'])->name('vehicle-type');

Route::get('/vehicle/creates', [App\Http\Controllers\VehicleController::class, 'creates'])->name('vehicle.creates');

Route::post('/vehicle/store', [App\Http\Controllers\VehicleController::class, 'store'])->name('vehicle-type.store');

Route::post('vehicle/switch', [App\Http\Controllers\VehicleController::class, 'toggalSwitch']);

Route::get('/vehicle/edits/{id}', [App\Http\Controllers\VehicleController::class, 'vehicleTypeEdit'])->name('vehicle.edits');

Route::put('/vehicle-type/update/{id}', [App\Http\Controllers\VehicleController::class, 'vehicleTypeUpdate'])->name('vehicle-type.update');

Route::get('/vehicle-type/delete/{id}', [App\Http\Controllers\VehicleController::class, 'deleteVehicle'])->name('vehicle-type.delete');

Route::post('vehicle-type/switch', [App\Http\Controllers\VehicleController::class, 'vehicleTypeSwitch']);



Route::get('/vehicle/vehicle', [App\Http\Controllers\VehicleController::class, 'vehicleList'])->name('vehicle');

Route::post('/vehicle/vehicle/create', [App\Http\Controllers\VehicleController::class, 'create'])->name('vehicle.create');

Route::get('/vehicle/vehicle/edit/{id}', [App\Http\Controllers\VehicleController::class, 'edit'])->name('vehicle.edit');

Route::put('/vehicle/vehicle/update/{id}', [App\Http\Controllers\VehicleController::class, 'update'])->name('vehicle.update');

Route::get('/vehicle/vehicle/delete/{id}', [App\Http\Controllers\VehicleController::class, 'delete'])->name('vehicle.delete');

Route::get('/vehicle/vehicle_create', [App\Http\Controllers\VehicleController::class, 'vehiclecreates'])->name('vehicle.vehicle_create');

Route::get('/vehicle/vehicle_edit/{id}', [App\Http\Controllers\VehicleController::class, 'edit'])->name('vehicle.vehicle_edit');



Route::get('/vehicle/vehicle-rent', [App\Http\Controllers\VehicleRentalController::class, 'vehicleRent'])->name('vehicle-rent');

Route::get('/vehicle/vehicle-rent/delete/{id}', [App\Http\Controllers\VehicleRentalController::class, 'delete'])->name('vehicle-rent.delete');

Route::get('/vehicle/vehicle-rent/show/{id}', [App\Http\Controllers\VehicleRentalController::class, 'show'])->name('vehicle-rent.show');

Route::get('/vehicle/vehicle-rent/ChangeStatus/{id}', [App\Http\Controllers\VehicleRentalController::class, 'ChangeStatus'])->name('vehicleRental.ChangeStatus');



Route::get('/vehicle-rental-type/index', [App\Http\Controllers\VehicleTypeRentalController::class, 'index'])->name('vehicle-rental-type');

Route::get('/vehicle-rental-type/create', [App\Http\Controllers\VehicleTypeRentalController::class, 'create'])->name('vehicle-rental-type.create');

Route::post('/vehicle-rental-type/store', [App\Http\Controllers\VehicleTypeRentalController::class, 'store'])->name('vehicle-rental-type.store');

Route::get('/vehicle-rental-type/edits/{id}', [App\Http\Controllers\VehicleTypeRentalController::class, 'edit'])->name('vehicle-rental-type.edit');

Route::put('/vehicle-rental-type/update/{id}', [App\Http\Controllers\VehicleTypeRentalController::class, 'update'])->name('vehicle-rental-type.update');

Route::get('/vehicle-rental-type/delete/{id}', [App\Http\Controllers\VehicleTypeRentalController::class, 'delete'])->name('vehicle-rental-type.delete');

Route::post('rental_vehicle_type/switch', [App\Http\Controllers\VehicleTypeRentalController::class, 'toggalSwitch']);



Route::get('/reports/userreport', [App\Http\Controllers\ReportController::class, 'userreport'])->name('userreport');

Route::get('/reports/downloadExcel', [App\Http\Controllers\ReportController::class, 'downloadExcel'])->name('userreport.downloadExcel');

Route::get('/reports/driverreport', [App\Http\Controllers\ReportController::class, 'driverreport'])->name('driverreport');

Route::get('/reports/downloadExcelDriver', [App\Http\Controllers\ReportController::class, 'downloadExcelDriver'])->name('driverreport.downloadExcelDriver');

Route::get('/reports/travelreport', [App\Http\Controllers\ReportController::class, 'travelreport'])->name('travelreport');

Route::get('/reports/downloadExcelTravel', [App\Http\Controllers\ReportController::class, 'downloadExcelTravel'])->name('travelreport.downloadExcelTravel');



Route::get('/coupons', [App\Http\Controllers\CouponController::class, 'index'])->name('coupons');

Route::get('/coupons/edit/{id}', [App\Http\Controllers\CouponController::class, 'edit'])->name('coupons.edit');

Route::get('/coupons/create', [App\Http\Controllers\CouponController::class, 'create'])->name('coupons.create');

Route::put('/coupons/update/{id}', [App\Http\Controllers\CouponController::class, 'updateDiscount'])->name('coupons.update');

Route::post('/coupons/store', [App\Http\Controllers\CouponController::class, 'store'])->name('coupons.store');

Route::get('/coupons/show/{id}', [App\Http\Controllers\CouponController::class, 'show'])->name('coupons.show');

Route::get('/coupons/delete/{id}', [App\Http\Controllers\CouponController::class, 'delete'])->name('coupons.delete');

Route::get('/coupons/changeStatus/{id}', [App\Http\Controllers\CouponController::class, 'changeStatus'])->name('coupons.changeStatus');

Route::post('coupon/switch', [App\Http\Controllers\CouponController::class, 'toggalSwitch']);

Route::get('/coupon/{id}', [App\Http\Controllers\CouponController::class, 'index'])->name('restaurants.coupons');

Route::get('/coupon/create/{id}', [App\Http\Controllers\CouponController::class, 'create']);



Route::get('driversPayouts/create', [App\Http\Controllers\DriversPayoutController::class, 'create'])->name('driversPayouts.create');

Route::post('driversPayouts/store', [App\Http\Controllers\DriversPayoutController::class, 'store'])->name('driversPayouts.store');

Route::get('driversPayouts', [App\Http\Controllers\DriversPayoutController::class, 'index'])->name('driversPayouts');



Route::get('walletstransaction', [App\Http\Controllers\TransactionController::class, 'index'])->name('walletstransaction');

Route::get('/walletstransaction/{id}', [App\Http\Controllers\TransactionController::class, 'index'])->name('users.walletstransaction');



Route::get('walletstransactions/driver/{id?}', [App\Http\Controllers\TransactionController::class, 'driverWallet'])->name('walletstransactions.driver');



Route::prefix('settings')->group(function () {

    Route::get('backup-restore', [App\Http\Controllers\DatabaseBackupController::class, 'index'])->name('database-backup.index');
    Route::get('backup-restore/download', [App\Http\Controllers\DatabaseBackupController::class, 'downloadBackup'])->name('database-backup.download');
    Route::post('backup-restore/restore', [App\Http\Controllers\DatabaseBackupController::class, 'restoreSql'])->name('database-backup.restore');
    Route::post('backup-restore/truncate-table', [App\Http\Controllers\DatabaseBackupController::class, 'truncateTable'])->name('database-backup.truncate-table');
    Route::post('backup-restore/purge-test-data', [App\Http\Controllers\DatabaseBackupController::class, 'purgeTestData'])->name('database-backup.purge-test-data');

    Route::get('app/globals', [App\Http\Controllers\SettingsController::class, 'globals'])->name('settings.app.globals');

    Route::get('app/social', [App\Http\Controllers\SettingsController::class, 'social'])->name('settings.app.social');

    Route::get('app/adminCommission', [App\Http\Controllers\SettingsController::class, 'adminCommission'])->name('settings.app.adminCommission');

    Route::get('app/radiosConfiguration', [App\Http\Controllers\SettingsController::class, 'radiosConfiguration'])->name('settings.app.radiosConfiguration');

    Route::get('app/notifications', [App\Http\Controllers\SettingsController::class, 'notifications'])->name('settings.app.notifications');



    Route::get('payment/stripe', [App\Http\Controllers\SettingsController::class, 'stripe'])->name('payment.stripe');

    Route::put('payment/stripeUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'stripeUpdate'])->name('payment.stripeUpdate');

    Route::get('payment/applepay', [App\Http\Controllers\SettingsController::class, 'applepay'])->name('payment.applepay');

    Route::put('payment/applepayUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'applepayUpdate'])->name('payment.applepayUpdate');

    Route::get('payment/razorpay', [App\Http\Controllers\SettingsController::class, 'razorpay'])->name('payment.razorpay');

    Route::put('payment/razorpayUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'razorpayUpdate'])->name('payment.razorpayUpdate');

    Route::get('payment/cod', [App\Http\Controllers\SettingsController::class, 'cod'])->name('payment.cod');

    Route::put('payment/codUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'codUpdate'])->name('payment.codUpdate');

    Route::get('payment/paypal', [App\Http\Controllers\SettingsController::class, 'paypal'])->name('payment.paypal');

    Route::put('payment/paypalUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'paypalUpdate'])->name('payment.paypalUpdate');

    Route::get('payment/wallet', [App\Http\Controllers\SettingsController::class, 'wallet'])->name('payment.wallet');

    Route::put('payment/walletUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'walletUpdate'])->name('payment.walletUpdate');

    Route::get('payment/payfast', [App\Http\Controllers\SettingsController::class, 'payfast'])->name('payment.payfast');

    Route::put('payment/payfastUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'payfastUpdate'])->name('payment.payfastUpdate');

    Route::get('payment/paystack', [App\Http\Controllers\SettingsController::class, 'paystack'])->name('payment.paystack');

    Route::put('payment/paystackUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'paystackUpdate'])->name('payment.paystackUpdate');

    Route::get('payment/flutterwave', [App\Http\Controllers\SettingsController::class, 'flutterwave'])->name('payment.flutterwave');

    Route::put('payment/flutterUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'flutterUpdate'])->name('payment.flutterUpdate');

    Route::get('payment/mercadopago', [App\Http\Controllers\SettingsController::class, 'mercadopago'])->name('payment.mercadopago');

    Route::put('payment/mercadopago/{id}', [App\Http\Controllers\SettingsController::class, 'mercadopagoUpdate'])->name('payment.mercadopagoUpdate');

    Route::get('payment/xendit', [App\Http\Controllers\SettingsController::class, 'xendit'])->name('payment.xendit');

    Route::put('payment/xenditUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'xenditUpdate'])->name('payment.xenditUpdate');

    Route::get('payment/orangepay', [App\Http\Controllers\SettingsController::class, 'orangepay'])->name('payment.orangepay');

    Route::put('payment/orangepayUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'orangepayUpdate'])->name('payment.orangepayUpdate');

    Route::get('payment/midtrans', [App\Http\Controllers\SettingsController::class, 'midtrans'])->name('payment.midtrans');

    Route::put('payment/midtransUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'midtransUpdate'])->name('payment.midtransUpdate');



    Route::get('brand', [App\Http\Controllers\SettingsController::class, 'brand'])->name('settings.brand');

    Route::get('brand/create', [App\Http\Controllers\SettingsController::class, 'brandCreate'])->name('settings.brand.create');

    Route::get('brand/edit/{id}', [App\Http\Controllers\SettingsController::class, 'brandEdit'])->name('settings.brand.edit');

    Route::get('carModel', [App\Http\Controllers\SettingsController::class, 'carModel'])->name('settings.carModel');

    Route::get('carModel/create', [App\Http\Controllers\SettingsController::class, 'carModelCreate'])->name('settings.carModel.create');

    Route::get('carModel/edit/{id}', [App\Http\Controllers\SettingsController::class, 'carModelEdit'])->name('settings.carModel.edit');



});



Route::prefix('administration_tools')->group(function () {



    Route::get('/country', [App\Http\Controllers\CountryController::class, 'index'])->name('country');

    Route::post('/country/store', [App\Http\Controllers\CountryController::class, 'store'])->name('country.store');

    Route::get('/country/show/{id}', [App\Http\Controllers\CountryController::class, 'show'])->name('country.show');

    Route::put('/country/update/{id}', [App\Http\Controllers\CountryController::class, 'update'])->name('country.update');

    Route::get('/country/changeStatus/{id}', [App\Http\Controllers\CountryController::class, 'changeStatus'])->name('country.changeStatus');

    Route::get('/country/create', [App\Http\Controllers\CountryController::class, 'create'])->name('country.create');

    Route::get('/country/edit/{id}', [App\Http\Controllers\CountryController::class, 'editCountry'])->name('country.edit');



    Route::get('/currency', [App\Http\Controllers\CurrencyController::class, 'index'])->name('currency');

    Route::get('/currency/create', [App\Http\Controllers\CurrencyController::class, 'createCurrency'])->name('currency.create');

    Route::get('/currency/edit/{id}', [App\Http\Controllers\CurrencyController::class, 'edit'])->name('currency.edit');

    Route::get('/currency/show/{id}', [App\Http\Controllers\CurrencyController::class, 'show'])->name('currency.show');

    Route::put('/currency/update/{id}', [App\Http\Controllers\CurrencyController::class, 'update'])->name('currency.update');

    Route::post('/currency/store', [App\Http\Controllers\CurrencyController::class, 'store'])->name('currency.store');

    Route::get('/currency/changeStatus/{id}', [App\Http\Controllers\CurrencyController::class, 'changeStatus'])->name('currency.changeStatus');

    Route::get('/currency/delete/{id}', [App\Http\Controllers\CurrencyController::class, 'delete'])->name('currency.delete');

    Route::get('/currency/change/{id}', [App\Http\Controllers\CurrencyController::class, 'currencyEdit'])->name('edit_currency');



    Route::get('/payment_method', [App\Http\Controllers\PaymentMethodController::class, 'index'])->name('payment_method');

    Route::get('/payment_method/show/{id}', [App\Http\Controllers\PaymentMethodController::class, 'show'])->name('payment_method.show');

    Route::get('/payment_method/changeStatus/{id}', [App\Http\Controllers\PaymentMethodController::class, 'changeStatus'])->name('payment_method.changeStatus');



    Route::get('/commission', [App\Http\Controllers\CommissionController::class, 'index'])->name('commission');

    Route::get('/commission/edit/{id}', [App\Http\Controllers\CommissionController::class, 'edit'])->name('commission.edit');

    Route::put('/commission/update/{id}', [App\Http\Controllers\CommissionController::class, 'update'])->name('commission.update');

    Route::get('/commission/show/{id}', [App\Http\Controllers\CommissionController::class, 'show'])->name('commission.show');

    Route::get('/commission/changeStatus/{id}', [App\Http\Controllers\CommissionController::class, 'changeStatus'])->name('commission.changeStatus');

    Route::get('/commission/search', [App\Http\Controllers\CommissionController::class, 'searchCommision'])->name('commision.search');

    Route::post('/subscription-model-switch', [App\Http\Controllers\CommissionController::class, 'toggalSwitchSubscriptionModel'])->name('subscription-model.switch');
    Route::put('bulk/commission/update', [App\Http\Controllers\CommissionController::class, 'bulkUpdate'])->name('bulk.commission.update');


    Route::get('/tax', [App\Http\Controllers\TaxController::class, 'index'])->name('tax');

    Route::get('/tax/create', [App\Http\Controllers\TaxController::class, 'create'])->name('tax.create');

    Route::post('/tax/store', [App\Http\Controllers\TaxController::class, 'store'])->name('tax.store');

    Route::get('/tax/edit/{id}', [App\Http\Controllers\TaxController::class, 'edit'])->name('tax.edit');

    Route::put('/tax/update/{id}', [App\Http\Controllers\TaxController::class, 'update'])->name('tax.update');

    Route::get('/tax/delete/{id}', [App\Http\Controllers\TaxController::class, 'delete'])->name('tax.delete');

    Route::get('/tax/show/{id}', [App\Http\Controllers\TaxController::class, 'show'])->name('tax.show');

    Route::get('/tax/changeStatus/{id}', [App\Http\Controllers\TaxController::class, 'changeStatus'])->name('tax.changeStatus');

    Route::get('/tax/search', [App\Http\Controllers\TaxController::class, 'searchTax'])->name('tax.search');



    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings');

    Route::get('/settings/edit/{id}', [App\Http\Controllers\SettingsController::class, 'edit'])->name('settings.edit');

    Route::post('/settings/update/{id}', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');



    Route::get('/homepageTemplate', [App\Http\Controllers\LandingPageTempController::class, 'index'])->name('homepageTemplate');

    Route::post('/homepageTemplate/save', [App\Http\Controllers\LandingPageTempController::class, 'save'])->name('homepageTemplate.save');



    Route::get('/terms_condition', [App\Http\Controllers\TermsAndConditionsController::class, 'index'])->name('terms_condition');

    Route::put('/terms_condition/update/{id}', [App\Http\Controllers\TermsAndConditionsController::class, 'update'])->name('terms_condition.update');

    Route::get('/privacy_policy', [App\Http\Controllers\TermsAndConditionsController::class, 'indexPrivacy'])->name('privacy_policy');

    Route::put('/privacy_policy/update/{id}', [App\Http\Controllers\TermsAndConditionsController::class, 'updatePrivacy'])->name('privacy_policy.update');



    Route::get('/driver_document', [App\Http\Controllers\DriverDocumentController::class, 'index'])->name('driver_document');

    Route::get('/driver_document/create', [App\Http\Controllers\DriverDocumentController::class, 'create'])->name('driver_document.create');

    Route::post('/driver_document/store', [App\Http\Controllers\DriverDocumentController::class, 'storeDocument'])->name('driver_document.store');

    Route::get('/driver_document/edit/{id}', [App\Http\Controllers\DriverDocumentController::class, 'edit'])->name('driver_document.edit');

    Route::put('/driver_document/update/{id}', [App\Http\Controllers\DriverDocumentController::class, 'documentUpdate'])->name('driver_document.update');

    Route::get('/driver_document/delete/{id}', [App\Http\Controllers\DriverDocumentController::class, 'deleteDocument'])->name('driver_document.delete');



    Route::get('email_template', [App\Http\Controllers\EmailTemplateController::class, 'index'])->name('email_template.index');

    Route::get('email_template/edit/{id}', [App\Http\Controllers\EmailTemplateController::class, 'edit'])->name('email_template.edit');

    Route::put('email_template/update/{id}', [App\Http\Controllers\EmailTemplateController::class, 'update'])->name('email_template.update');



});



Route::get('customer-care', [App\Http\Controllers\CustomerCareController::class, 'index'])->name('customer-care.index');
Route::post('customer-care', [App\Http\Controllers\CustomerCareController::class, 'update'])->name('customer-care.update');
Route::get('service-requests', [App\Http\Controllers\ServiceRequestController::class, 'index'])->name('service_requests');
Route::get('service-requests/show/{id}', [App\Http\Controllers\ServiceRequestController::class, 'show'])->name('service_requests.show');
Route::post('service-requests/retry/{id}', [App\Http\Controllers\ServiceRequestController::class, 'retrySearch'])->name('service_requests.retry');
Route::post('service-requests/cancel/{id}', [App\Http\Controllers\ServiceRequestController::class, 'cancelRequest'])->name('service_requests.cancel');
Route::get('home-services', [App\Http\Controllers\HomeServiceController::class, 'index'])->name('home_services.index');
Route::get('home-services/create', [App\Http\Controllers\HomeServiceController::class, 'create'])->name('home_services.create');
Route::post('home-services', [App\Http\Controllers\HomeServiceController::class, 'store'])->name('home_services.store');
Route::get('home-services/skills/{parentId}', [App\Http\Controllers\HomeServiceController::class, 'getSkills'])->name('home_services.getSkills');
Route::get('home-services/sub-skills/{skillId}', [App\Http\Controllers\HomeServiceController::class, 'getSubSkills'])->name('home_services.getSubSkills');
Route::post('home-services/skill', [App\Http\Controllers\HomeServiceController::class, 'storeSkill'])->name('home_services.storeSkill');
Route::post('home-services/sub-skill', [App\Http\Controllers\HomeServiceController::class, 'storeSubSkill'])->name('home_services.storeSubSkill');
Route::get('home-services/edit/{id}', [App\Http\Controllers\HomeServiceController::class, 'edit'])->name('home_services.edit');
Route::post('home-services/update/{id}', [App\Http\Controllers\HomeServiceController::class, 'update'])->name('home_services.update');
Route::post('home-services/toggle/{id}', [App\Http\Controllers\HomeServiceController::class, 'toggleStatus'])->name('home_services.toggleStatus');
Route::delete('home-services/{id}', [App\Http\Controllers\HomeServiceController::class, 'destroy'])->name('home_services.destroy');


Route::get('complaints/delete/{id}', [App\Http\Controllers\ComplaintsController::class, 'deleteComplaints'])->name('complaints.delete');

Route::get('complaints/show/{id}', [App\Http\Controllers\ComplaintsController::class, 'show'])->name('complaints.show');

Route::post('complaints/update', [App\Http\Controllers\ComplaintsController::class, 'update'])->name('complaints.update');



Route::get('sos', [App\Http\Controllers\SosController::class, 'index'])->name('sos');

Route::get('/sos/show/{id}', [App\Http\Controllers\SosController::class, 'show'])->name('sos.show');

Route::get('/sos/delete/{id}', [App\Http\Controllers\SosController::class, 'deleteSos'])->name('sos.delete');

Route::put('/sos/update/{id}', [App\Http\Controllers\SosController::class, 'sosUpdate'])->name('sos.update');



Route::get('/car_model', [App\Http\Controllers\CarModelController::class, 'index'])->name('car_model');

Route::get('/car_model/create', [App\Http\Controllers\CarModelController::class, 'create'])->name('car_model.create');

Route::get('/car_model/edit/{id}', [App\Http\Controllers\CarModelController::class, 'edit'])->name('car_model.edit');

Route::get('/car_model/delete/{id}', [App\Http\Controllers\CarModelController::class, 'deleteCarModel'])->name('car_model.delete');

Route::put('car_model/update/{id}', [App\Http\Controllers\CarModelController::class, 'UpdateCarModel'])->name('car_model.update');

Route::post('/car_model/storecarmodel', [App\Http\Controllers\CarModelController::class, 'storecarmodel'])->name('car_model.storecarmodel');

Route::post('carModel/switch', [App\Http\Controllers\CarModelController::class, 'toggalSwitch']);



Route::get('brands', [App\Http\Controllers\BrandController::class, 'index'])->name('brand');

Route::get('brands/create', [App\Http\Controllers\BrandController::class, 'createCurrency'])->name('brand.create');

Route::post('brands/create', [App\Http\Controllers\BrandController::class, 'store'])->name('brand.store');

Route::get('brands/edit/{id}', [App\Http\Controllers\BrandController::class, 'edit'])->name('brand.edit');

Route::put('brands/update/{id}', [App\Http\Controllers\BrandController::class, 'update'])->name('brand.update');

Route::get('brands/delete/{id}', [App\Http\Controllers\BrandController::class, 'deleteBrand'])->name('brand.delete');

Route::get('brands/show/{id}', [App\Http\Controllers\BrandController::class, 'show'])->name('brand.show');

Route::post('brand/switch', [App\Http\Controllers\BrandController::class, 'toggalSwitch']);



Route::post('currency/switch', [App\Http\Controllers\CurrencyController::class, 'toggalSwitch'])->name('currency.switch');

Route::post('country/switch', [App\Http\Controllers\CountryController::class, 'toggalSwitch']);

Route::post('commission/switch', [App\Http\Controllers\CommissionController::class, 'toggalSwitch']);

Route::post('tax/switch', [App\Http\Controllers\TaxController::class, 'toggalSwitch']);
Route::post('tax/toggle-method', [App\Http\Controllers\TaxController::class, 'togglePaymentMethod']);

Route::post('driver_document/switch', [App\Http\Controllers\DriverDocumentController::class, 'toggalSwitch'])->name('driver_document.switch');



Route::get('/payoutRequest', [App\Http\Controllers\PayoutRequestController::class, 'payout'])->name('payoutRequests');

Route::get('/payoutRequest/{id}', [App\Http\Controllers\PayoutRequestController::class, 'payout'])->name('payoutRequests.view');

Route::post('driver/getbankdetails', [App\Http\Controllers\PayoutRequestController::class, 'getBankDetails']);

Route::post('withdrawal/accept', [App\Http\Controllers\PayoutRequestController::class, 'acceptWithdrawal']);

Route::post('withdrawal/reject', [App\Http\Controllers\PayoutRequestController::class, 'rejectWithdrawal']);

Route::get('/get-settings', [App\Http\Controllers\SettingsController::class, 'getSettings'])->name('get-settings');



Route::get('/dispatcher-users/create', [App\Http\Controllers\DispatcherController::class, 'createUser'])->name('dispatcher-users.create');

Route::get('/dispatcher-users', [App\Http\Controllers\DispatcherController::class, 'index'])->name('dispatcher-users');

Route::post('/dispatcher-users/storeuser', [App\Http\Controllers\DispatcherController::class, 'storeUser'])->name('dispatcher-users.store');

Route::get('/dispatcher-users/edit/{id}', [App\Http\Controllers\DispatcherController::class, 'editUser'])->name('dispatcher-users.edit');

Route::get('/dispatcher-users/delete/{id}', [App\Http\Controllers\DispatcherController::class, 'deleteUser'])->name('dispatcher-users.delete');

Route::put('dispatcher-users/update/{id}', [App\Http\Controllers\DispatcherController::class, 'userUpdate'])->name('dispatcher-users.update');
Route::post('/switch', [App\Http\Controllers\UserController::class, 'toggalSwitch']);

Route::post('/dispatcher-users-switch', [App\Http\Controllers\DispatcherController::class, 'toggalSwitch']);

Route::get('/dispatcher-users/show/{id}', [App\Http\Controllers\DispatcherController::class, 'userShow'])->name('dispatcher-users.show');

Route::get('/dispatcher-users/changestatus/{id}', [App\Http\Controllers\DispatcherController::class, 'userChangeStatus'])->name('dispatcher-users.changestatus');



Route::get('/map', [App\Http\Controllers\MapController::class, 'index'])->name('map');

Route::any('/map/get_ride_info', [App\Http\Controllers\MapController::class, 'getRideInfo'])->name('map.getrideinfo');

Route::get('parcel/map', [App\Http\Controllers\ParcelMapController::class, 'index'])->name('parcel.map');

Route::any('parcel/map/get_ride_info', [App\Http\Controllers\ParcelMapController::class, 'getRideInfo'])->name('parcel.map.getrideinfo');



Route::get('/parcel-category/create', [App\Http\Controllers\ParcelCategoryController::class, 'create'])->name('parcel-category.create');

Route::get('/parcel-category', [App\Http\Controllers\ParcelCategoryController::class, 'index'])->name('parcel-category');

Route::post('/parcel-category/store', [App\Http\Controllers\ParcelCategoryController::class, 'store'])->name('parcel-category.store');

Route::get('/parcel-category/edit/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'edit'])->name('parcel-category.edit');

Route::get('/parcel-category/delete/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'delete'])->name('parcel-category.delete');

Route::put('parcel-category/update/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'update'])->name('parcel-category.update');

Route::post('/parcel-category-switch', [App\Http\Controllers\ParcelCategoryController::class, 'toggalSwitch']);

Route::get('/parcel-category/changestatus/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'changeStatus'])->name('parcel-category.changestatus');



Route::get('zone', [App\Http\Controllers\ZoneController::class, 'index'])->name('zone');

Route::get('zone/create', [App\Http\Controllers\ZoneController::class, 'create'])->name('zone.create');

Route::post('zone/store', [App\Http\Controllers\ZoneController::class, 'store'])->name('zone.store');

Route::get('zone/edit/{id}', [App\Http\Controllers\ZoneController::class, 'edit'])->name('zone.edit');

Route::put('zone/update/{id}', [App\Http\Controllers\ZoneController::class, 'update'])->name('zone.update');

Route::get('zone/delete/{id}', [App\Http\Controllers\ZoneController::class, 'delete'])->name('zone.delete');

Route::post('zone/switch', [App\Http\Controllers\ZoneController::class, 'toggalSwitch'])->name('zone.switch');



Route::get('/banners/create', [App\Http\Controllers\BannersController::class, 'create'])->name('banners.create');

Route::get('/banners', [App\Http\Controllers\BannersController::class, 'index'])->name('banners');

Route::post('/banners/store', [App\Http\Controllers\BannersController::class, 'store'])->name('banners.store');

Route::get('/banners/edit/{id}', [App\Http\Controllers\BannersController::class, 'edit'])->name('banners.edit');

Route::get('/banners/delete/{id}', [App\Http\Controllers\BannersController::class, 'delete'])->name('banners.delete');

Route::put('banners/update/{id}', [App\Http\Controllers\BannersController::class, 'update'])->name('banners.update');

Route::post('/banners-switch', [App\Http\Controllers\BannersController::class, 'toggalSwitch']);

Route::get('/banners/changestatus/{id}', [App\Http\Controllers\BannersController::class, 'changeStatus'])->name('banners.changestatus');



Route::get('/on-boarding', [App\Http\Controllers\OnBoardingController::class, 'index'])->name('on-boarding');

Route::get('/on-boarding/edit/{id}', [App\Http\Controllers\OnBoardingController::class, 'edit'])->name('on-boarding.edit');

Route::put('/on-boarding/update/{id}', [App\Http\Controllers\OnBoardingController::class, 'update'])->name('on-boarding.update');

Route::get('/subscription-plans', [App\Http\Controllers\SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
Route::get('/current-subscriber/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'currentSubscriberList'])->name('current-subscriber.list');
Route::get('/subscription-plans/create', [App\Http\Controllers\SubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
Route::post('/subscription-plans/store', [App\Http\Controllers\SubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
Route::get('/subscription-plans/edit/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
Route::put('subscription-plans/update/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
Route::get('/subscription-plans/delete/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'delete'])->name('subscription-plans.delete');
Route::get('/driver/subscription-plan/history', [App\Http\Controllers\SubscriptionPlanController::class, 'SubscriptionHistory'])->name('driver.subscriptionHistory');
Route::post('/subscription-plans-switch', [App\Http\Controllers\SubscriptionPlanController::class, 'toggalSwitch']);
Route::get('/subscription-history/delete/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'deleteHistory'])->name('subscription-history.delete');
Route::post('/get-plan-detail', [App\Http\Controllers\DriverController::class, 'getPlanDetail'])->name('get-plan-detail');
Route::post('/subscription-checkout', [App\Http\Controllers\DriverController::class, 'subscriptionCheckout'])->name('subscription-checkout');
Route::put('subscription-limit/update/{id}', [App\Http\Controllers\DriverController::class, 'updateLimit'])->name('subscription-limit.update');

// ── Consumer Premium Plans ────────────────────────────────────────────────────
Route::get('/consumer-plans', [App\Http\Controllers\ConsumerPlanController::class, 'index'])->name('consumer-plans.index');
Route::get('/consumer-plans/create', [App\Http\Controllers\ConsumerPlanController::class, 'create'])->name('consumer-plans.create');
Route::post('/consumer-plans/store', [App\Http\Controllers\ConsumerPlanController::class, 'store'])->name('consumer-plans.store');
Route::get('/consumer-plans/edit/{id}', [App\Http\Controllers\ConsumerPlanController::class, 'edit'])->name('consumer-plans.edit');
Route::put('/consumer-plans/update/{id}', [App\Http\Controllers\ConsumerPlanController::class, 'update'])->name('consumer-plans.update');
Route::get('/consumer-plans/delete/{id}', [App\Http\Controllers\ConsumerPlanController::class, 'delete'])->name('consumer-plans.delete');
Route::post('/consumer-plans/toggle', [App\Http\Controllers\ConsumerPlanController::class, 'toggleStatus'])->name('consumer-plans.toggle');

Route::get('/export/{type}/{model}', [App\Http\Controllers\ExportController::class, 'export'])->name('export.data');

Route::get('/logs', [App\Http\Controllers\LogsController::class, 'index'])->name('logs');
Route::get('/logs/clear', [App\Http\Controllers\LogsController::class, 'clear'])->name('logs.clear');

// ── Dynamic API Keys ────────────────────────────────────────────────────────
Route::get('/administration_tools/api-keys', [App\Http\Controllers\ApiKeySettingController::class, 'index'])->name('api-keys.index');
Route::post('/administration_tools/api-keys/store', [App\Http\Controllers\ApiKeySettingController::class, 'storeOrUpdate'])->name('api-keys.store');
Route::post('/administration_tools/api-keys/toggle', [App\Http\Controllers\ApiKeySettingController::class, 'toggleStatus'])->name('api-keys.toggle');

// ── Wallet Growth Engine ───────────────────────────────────────────────────
Route::get('/wallet-growth', [App\Http\Controllers\WalletGrowthController::class, 'index'])->name('wallet-growth.index');
Route::post('/wallet-growth/update', [App\Http\Controllers\WalletGrowthController::class, 'update'])->name('wallet-growth.update');
Route::post('/wallet-growth/run', [App\Http\Controllers\WalletGrowthController::class, 'runManualGrowth'])->name('wallet-growth.run');

// ── Referral Engine ────────────────────────────────────────────────────────
Route::get('/referral-engine', [App\Http\Controllers\ReferralRewardController::class, 'index'])->name('referral.index');
Route::post('/referral-engine/update', [App\Http\Controllers\ReferralRewardController::class, 'update'])->name('referral.update');

// ── Invoice Download ───────────────────────────────────────────────────────
Route::get('/invoice/{id}/download', [App\Http\Controllers\InvoiceController::class, 'downloadInvoice'])->name('invoice.download');

// ── Campaigns ──────────────────────────────────────────────────────────────
Route::get('/campaigns', [App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns.index');
Route::post('/campaigns/send', [App\Http\Controllers\CampaignController::class, 'sendCampaign'])->name('campaigns.send');

// ── Customer Support Live Chat & Quick Questions ────────────────────────────
Route::get('/support-chats', [App\Http\Controllers\SupportChatController::class, 'index'])->name('support.chat.index');
Route::get('/support-chats/tickets', [App\Http\Controllers\SupportChatController::class, 'getTickets'])->name('support.chat.tickets');
Route::get('/support-chats/messages/{ticketId}', [App\Http\Controllers\SupportChatController::class, 'getMessages'])->name('support.chat.messages');
Route::post('/support-chats/send', [App\Http\Controllers\SupportChatController::class, 'sendReply'])->name('support.chat.send');
Route::post('/support-chats/toggle-status', [App\Http\Controllers\SupportChatController::class, 'toggleStatus'])->name('support.chat.toggleStatus');

Route::get('/support-questions', [App\Http\Controllers\SupportQuestionController::class, 'index'])->name('support.questions.index');
Route::post('/support-questions/store', [App\Http\Controllers\SupportQuestionController::class, 'store'])->name('support.questions.store');
Route::post('/support-questions/update/{id}', [App\Http\Controllers\SupportQuestionController::class, 'update'])->name('support.questions.update');
Route::get('/support-questions/delete/{id}', [App\Http\Controllers\SupportQuestionController::class, 'destroy'])->name('support.questions.delete');
Route::post('/support-questions/toggle-status/{id}', [App\Http\Controllers\SupportQuestionController::class, 'toggleStatus'])->name('support.questions.toggleStatus');

// ── App Version Control & Play Store Upgrade ────────────────────────────────
Route::get('/app-version-control', [App\Http\Controllers\AppVersionControlController::class, 'index'])->name('app-version-control.index');
Route::post('/app-version-control/update/{id}', [App\Http\Controllers\AppVersionControlController::class, 'update'])->name('app-version-control.update');
Route::post('/app-version-control/toggle-force/{id}', [App\Http\Controllers\AppVersionControlController::class, 'toggleForce'])->name('app-version-control.toggleForce');
Route::post('/app-version-control/toggle-maintenance/{id}', [App\Http\Controllers\AppVersionControlController::class, 'toggleMaintenance'])->name('app-version-control.toggleMaintenance');

// ── Driver & Partner Kit Management ─────────────────────────────────────────
Route::get('/driver-kits', [App\Http\Controllers\DriverKitController::class, 'index'])->name('driver-kits.index');
Route::post('/driver-kits/update/{id}', [App\Http\Controllers\DriverKitController::class, 'update'])->name('driver-kits.update');
Route::post('/driver-kits/toggle-compulsory/{id}', [App\Http\Controllers\DriverKitController::class, 'toggleCompulsory'])->name('driver-kits.toggleCompulsory');
Route::post('/driver-kits/toggle-active/{id}', [App\Http\Controllers\DriverKitController::class, 'toggleActive'])->name('driver-kits.toggleActive');
Route::get('/driver-kits/orders', [App\Http\Controllers\DriverKitController::class, 'orders'])->name('driver-kits.orders');
Route::post('/driver-kits/orders/update-status/{id}', [App\Http\Controllers\DriverKitController::class, 'updateOrderStatus'])->name('driver-kits.orders.updateStatus');

// ── Web Onboarding Kit Purchase Page (WebView) ──────────────────────────────
Route::get('/onboarding/kit-purchase', [App\Http\Controllers\DriverKitWebController::class, 'showCheckout'])->name('driver-kits.webCheckout');
Route::post('/onboarding/kit-purchase/submit', [App\Http\Controllers\DriverKitWebController::class, 'submitCheckout'])->name('driver-kits.webSubmit');

// ── Terms & Privacy Direct URL Aliases ─────────────────────────────────────
Route::get('/terms_condition', [App\Http\Controllers\TermsAndConditionsController::class, 'index']);
Route::put('/terms_condition/update/{id}', [App\Http\Controllers\TermsAndConditionsController::class, 'update']);
Route::get('/privacy_policy', [App\Http\Controllers\TermsAndConditionsController::class, 'indexPrivacy']);
Route::put('/privacy_policy/update/{id}', [App\Http\Controllers\TermsAndConditionsController::class, 'updatePrivacy']);
