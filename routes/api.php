<?php



use App\Http\Controllers\API\v1\AddAmountController;

use App\Http\Controllers\API\v1\AddComplaintsController;

use App\Http\Controllers\API\v1\BankAccountDetailsController;



use App\Http\Controllers\API\v1\CancelController;

use App\Http\Controllers\API\v1\canceledLocationController;

use App\Http\Controllers\API\v1\CancelRequeteBookController;

use App\Http\Controllers\API\v1\CancelRequeteBookingController;

use App\Http\Controllers\API\v1\CancelRequeteController;

use App\Http\Controllers\API\v1\CarDriverConfirmController;

use App\Http\Controllers\API\v1\CarImagesDriversNameController;

use App\Http\Controllers\API\v1\CarServiceBookController;

use App\Http\Controllers\API\v1\CarServiceBookHistoryController;

use App\Http\Controllers\API\v1\ChangeStatusControlller;

use App\Http\Controllers\API\v1\ChangeStatusForpaymentController;

use App\Http\Controllers\API\v1\CompleteRequeteController;

use App\Http\Controllers\API\v1\ConfirmedRequeteBookController;

use App\Http\Controllers\API\v1\ConfirmRequeteController;

use App\Http\Controllers\API\v1\ContactUsController;

use App\Http\Controllers\API\v1\DashboardController;

use App\Http\Controllers\API\v1\DriverDashboardStatsController;

use App\Http\Controllers\API\v1\DeleteFavoriteRideController;

use App\Http\Controllers\API\v1\DeleteUserController;

use App\Http\Controllers\API\v1\DiscountController;

use App\Http\Controllers\API\v1\DocumentsController;

use App\Http\Controllers\API\v1\DriverController;

use App\Http\Controllers\API\v1\DriverReviewController;

use App\Http\Controllers\API\v1\DriversVehicleController;

use App\Http\Controllers\API\v1\DriverWalletHistoryController;

use App\Http\Controllers\API\v1\DriverWithdrawalsController;

use App\Http\Controllers\API\v1\ExistingUserController;

use App\Http\Controllers\API\v1\FavoriteRequeteUserController;

use App\Http\Controllers\API\v1\FavoriteRideController;

use App\Http\Controllers\API\v1\ForgotPersonalIteamController;

use App\Http\Controllers\API\v1\generateotpController;

use App\Http\Controllers\API\v1\GetDriverWithdrawalsController;

use App\Http\Controllers\API\v1\getFcmController;

use App\Http\Controllers\API\v1\GetProfileByPhoneController;

use App\Http\Controllers\API\v1\GetVehicleController;

use App\Http\Controllers\API\v1\LaunguageController;

use App\Http\Controllers\API\v1\LocationController;

use App\Http\Controllers\API\v1\ModelListController;

use App\Http\Controllers\API\v1\NoteController;

use App\Http\Controllers\API\v1\NotificationListController;

use App\Http\Controllers\API\v1\NotifyController;

use App\Http\Controllers\API\v1\OldUserPhotoController;

use App\Http\Controllers\API\v1\OnrideRequeteBookController;

use App\Http\Controllers\API\v1\OnrideRequeteController;

use App\Http\Controllers\API\v1\OnboardingController as DriverOnboardingController;
use App\Http\Controllers\API\v1\OtpVerificationController;

use App\Http\Controllers\API\v1\PayFastController;

use App\Http\Controllers\API\v1\PaymentByCashController;

use App\Http\Controllers\API\v1\PaymentMethodController;

use App\Http\Controllers\API\v1\payments\PaymentController;

use App\Http\Controllers\API\v1\payments\RazorPayController;

use App\Http\Controllers\API\v1\PaymentSettingController;

use App\Http\Controllers\API\v1\PayRequeteController;

use App\Http\Controllers\API\v1\PayRequeteWalletController;

use App\Http\Controllers\API\v1\PositionController;

use App\Http\Controllers\API\v1\privacyPolicyController;

use App\Http\Controllers\API\v1\ReqFeelSafeController;

use App\Http\Controllers\API\v1\ReqNotFeelSafeController;

use App\Http\Controllers\API\v1\RequeteBookCancelController;

use App\Http\Controllers\API\v1\RequeteBookCancelUserController;

use App\Http\Controllers\API\v1\RequeteBookConfirmController;

use App\Http\Controllers\API\v1\RequeteBookConfirmUserController;

use App\Http\Controllers\API\v1\RequeteBookController;

use App\Http\Controllers\API\v1\RequeteBookRejectedController;

use App\Http\Controllers\API\v1\RequeteBookUserappController;

use App\Http\Controllers\API\v1\RequeteCompleteController;

use App\Http\Controllers\API\v1\RequeteConfirmController;

use App\Http\Controllers\API\v1\RequeteController;

use App\Http\Controllers\API\v1\RequeteOnrideController;

use App\Http\Controllers\API\v1\RequeteRegisterController;

use App\Http\Controllers\API\v1\RequeteRejectController;

use App\Http\Controllers\API\v1\RequeteUserappCanceledController;

use App\Http\Controllers\API\v1\RequeteUserappCompleteController;

use App\Http\Controllers\API\v1\RequeteUserappConfirmationController;

use App\Http\Controllers\API\v1\RequeteUserappController;

use App\Http\Controllers\API\v1\RequeteUserappOnRideController;

use App\Http\Controllers\API\v1\ResertPasswordController;

use App\Http\Controllers\API\v1\RideDetailsController;

use App\Http\Controllers\API\v1\SendResetPasswordOtpController;

use App\Http\Controllers\API\v1\SetCarServiceBookController;

use App\Http\Controllers\API\v1\SetLocationController;

use App\Http\Controllers\API\v1\SetRejectedRequeteController;

use App\Http\Controllers\API\v1\DriverDispatchController;

use App\Http\Controllers\API\v1\SettingsController;

use App\Http\Controllers\API\v1\SosController;

use App\Http\Controllers\API\v1\taxiController;

use App\Http\Controllers\API\v1\termsofConditionController;

use App\Http\Controllers\API\v1\TransactionController;

use App\Http\Controllers\API\v1\UpdatefcmController;

use App\Http\Controllers\API\v1\UseGenderController;

use App\Http\Controllers\API\v1\User_LoginController;

use App\Http\Controllers\API\v1\UserAddressController;

use App\Http\Controllers\API\v1\AuthOtpController;

use App\Http\Controllers\API\v1\UserController;

use App\Http\Controllers\API\v1\UserEmailController;

use App\Http\Controllers\API\v1\UserLicenceController;

use App\Http\Controllers\API\v1\UserLoginController;

use App\Http\Controllers\API\v1\UsermdpController;

use App\Http\Controllers\API\v1\UserNameController;

use App\Http\Controllers\API\v1\UserNicController;

use App\Http\Controllers\API\v1\UserNoteController;

use App\Http\Controllers\API\v1\UserPendingPaymentController;

use App\Http\Controllers\API\v1\UserPhoneController;

use App\Http\Controllers\API\v1\UserPhotoController;

use App\Http\Controllers\API\v1\UserPreNameController;

use App\Http\Controllers\API\v1\UserRoadWorthyDocController;

use App\Http\Controllers\API\v1\VehicleController;

use App\Http\Controllers\API\v1\WalletController;

use App\Http\Controllers\API\v1\DriverRideReviewController;

use App\Http\Controllers\API\v1\GetUserReferralCode;

use App\Http\Controllers\API\v1\ParcelCategoryController;

use App\Http\Controllers\API\v1\GetParcelOrdersController;

use App\Http\Controllers\API\v1\ParcelRegisterController;

use App\Http\Controllers\API\v1\ParcelConfirmController;

use App\Http\Controllers\API\v1\ParcelOnRideController;

use App\Http\Controllers\API\v1\ParcelCompleteController;

use App\Http\Controllers\API\v1\ParcelRejectController;

use App\Http\Controllers\API\v1\PayParcelRequestController;

use App\Http\Controllers\API\v1\PaymentByCashParcelController;

use App\Http\Controllers\API\v1\PayParcelWalletController;

use App\Http\Controllers\API\v1\ParcelCanceledController;

use App\Http\Controllers\API\v1\SearchDriverParcelOrdersController;

use App\Http\Controllers\API\v1\ZoneController;

use App\Http\Controllers\API\v1\BannersController;

use App\Http\Controllers\API\v1\UserProfileUpdateController;

use App\Http\Controllers\API\v1\OnboardingController as OnBoardingController;

use App\Http\Controllers\API\v1\SubscriptionPlanController;
use App\Http\Controllers\API\v1\ProductController;
use App\Http\Controllers\API\v1\MarketplaceOrderController;
use App\Http\Controllers\API\v1\UserCategoryController as UserCategoryAPIController;
use App\Http\Controllers\API\v1\MedicalCashbackController;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;



/*

|--------------------------------------------------------------------------

| API Routes

|--------------------------------------------------------------------------

|

| Here is where you can register API routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| is assigned the "api" middleware group. Enjoy building your API!

|

*/



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {

    return $request->user();

});



Route::group(['middleware' => ['envKeyAuth']], function () {

    /*Guest Request*/

    Route::post('v1/user/', [UserController::class, 'register']);

    Route::post('v1/user-login/', [UserLoginController::class, 'login']);

    // ── Phone + Email OTP Auth (New Flow) ─────────────────────────────────────
    // Check if user exists by phone
    Route::post('v1/auth/check-user',              [AuthOtpController::class, 'checkUser']);
    // Login by MPIN
    Route::post('v1/auth/login-by-mpin',           [AuthOtpController::class, 'loginByMpin']);
    // Verify MPIN
    Route::post('v1/verify-mpin',                  [AuthOtpController::class, 'verifyMpin']);
    // Reset MPIN via OTP
    Route::post('v1/auth/reset-mpin',              [AuthOtpController::class, 'resetMpin']);
    // Apply referral code (from profile screen, after registration)
    Route::post('v1/auth/apply-referral',          [AuthOtpController::class, 'applyReferral']);

    // Create account with Name + Phone OTP + MPIN
    Route::post('v1/auth/register-simple',         [AuthOtpController::class, 'registerSimple']);
    // Update existing driver category
    Route::post('v1/auth/update-driver-category',  [AuthOtpController::class, 'updateDriverCategory']);
    // Get/toggle specific driver service categories
    Route::get('v1/driver/services',               [AuthOtpController::class, 'getDriverServices']);
    Route::post('v1/driver/services/toggle',       [AuthOtpController::class, 'toggleDriverService']);

    // Signup: Step 1 → send phone OTP (dummy 1234, TODO: replace with SMS gateway)
    Route::post('v1/auth/send-phone-otp',         [AuthOtpController::class, 'sendPhoneOtp']);
    // Signup: Step 2 → verify phone OTP
    Route::post('v1/auth/verify-phone-otp',        [AuthOtpController::class, 'verifyPhoneOtp']);
    // Signup: Step 4 → send email OTP (real email via Hostinger SMTP)
    Route::post('v1/auth/send-email-otp',          [AuthOtpController::class, 'sendEmailOtp']);
    // Signup: Step 5 → verify email OTP and create account
    Route::post('v1/auth/verify-email-otp-register', [AuthOtpController::class, 'verifyEmailOtpAndRegister']);
    // Login: Step 1 → find user by phone, send OTP to registered email
    Route::post('v1/auth/login-by-phone',          [AuthOtpController::class, 'loginByPhone']);
    // Login: Step 2 → verify login email OTP and return session
    Route::post('v1/auth/verify-login-email-otp',  [AuthOtpController::class, 'verifyLoginEmailOtp']);
    // ─────────────────────────────────────────────────────────────────────────



    Route::post('v1/existing-user/', [ExistingUserController::class, 'getData']);

    Route::post('v1/update-user-nic/', [UserNicController::class, 'updateUserNic']);

    Route::get('v1/language/', [LaunguageController::class, 'getData']);

    Route::get('v1/privacy-policy/', [privacyPolicyController::class, 'getData']);

    Route::get('v1/terms-of-condition/', [termsofConditionController::class, 'getData']);

    Route::get('v1/settings/', [SettingsController::class, 'getData']);

    Route::get('v1/customer-care/', [\App\Http\Controllers\API\v1\CustomerCareAPIController::class, 'getCustomerCare']);

    Route::post('v1/profilebyphone/', [GetProfileByPhoneController::class, 'getData']);



    Route::get('v1/documents/', [DocumentsController::class, 'getData']);

    Route::post('v1/driver-documents-add/', [DocumentsController::class, 'addDriverDocuments']);

    Route::post('v1/driver-documents-update/', [DocumentsController::class, 'updateDriverDocuments']);

    Route::get('v1/driver-documents/', [DocumentsController::class, 'getDriverDocuments']);
    Route::get('v1/driver/onboarding/init', [\App\Http\Controllers\API\v1\OnboardingController::class, 'init']);
    Route::post('v1/driver/onboarding/submit', [\App\Http\Controllers\API\v1\OnboardingController::class, 'submit']);

    Route::get('v1/zone/', [ZoneController::class, 'getData']);

    Route::get('v1/on-boarding/', [\App\Http\Controllers\API\v1\OnboardingController::class, 'getData']);

});

// Marketplace Public & Webview Routes (Accessible without apikey header in Webview)
Route::get('v1/marketplace/products', [ProductController::class, 'index']);
Route::get('v1/marketplace/products/{id}', [ProductController::class, 'show']);
Route::get('v1/marketplace/categories', [ProductController::class, 'categories']);
Route::get('v1/marketplace/user-profile', [ProductController::class, 'userProfile']);
Route::get('v1/marketplace/my-products', [ProductController::class, 'myProducts']);
Route::get('v1/marketplace/products/{id}/progress', [ProductController::class, 'verificationProgress']);
Route::post('v1/marketplace/products', [ProductController::class, 'store']);

// Edit / Update Product Routes (Supports PUT, PATCH, and POST)
Route::match(['put', 'patch', 'post'], 'v1/marketplace/products/{id}', [ProductController::class, 'update']);
Route::match(['put', 'patch', 'post'], 'v1/marketplace/products/{id}/update', [ProductController::class, 'update']);

// Delete Product Routes (Supports DELETE and POST)
Route::match(['delete', 'post'], 'v1/marketplace/products/{id}', [ProductController::class, 'destroy']);
Route::match(['delete', 'post'], 'v1/marketplace/products/{id}/delete', [ProductController::class, 'destroy']);

Route::post('v1/marketplace/upload-image', [ProductController::class, 'uploadImage']);
Route::get('v1/marketplace/image/{filename}', [ProductController::class, 'serveMarketplaceImage'])->where('filename', '.*');

// Marketplace Order Routes
Route::match(['get', 'post'], 'v1/marketplace/checkout-summary', [MarketplaceOrderController::class, 'checkoutSummary']);
Route::post('v1/marketplace/orders', [MarketplaceOrderController::class, 'store']);
Route::get('v1/marketplace/orders/buyer', [MarketplaceOrderController::class, 'buyerOrders']);
Route::get('v1/marketplace/orders/seller', [MarketplaceOrderController::class, 'sellerOrders']);
Route::get('v1/marketplace/orders/{id}', [MarketplaceOrderController::class, 'show']);
Route::post('v1/marketplace/orders/{id}/status', [MarketplaceOrderController::class, 'updateStatus']);



Route::group(['middleware' => ['apiKeyAuth']], function () {

    /*Auth Request*/

    Route::get('v1/users/', [UserController::class, 'index']);

    Route::post('v1/vehicle/', [VehicleController::class, 'register']);

    Route::post('v1/update-vehicle-numberplate/', [VehicleController::class, 'updateVehicle']);

    Route::post('v1/update-vehicle-color/', [VehicleController::class, 'updateVehicleColor']);

    Route::post('v1/update-vehicle-brand/', [VehicleController::class, 'updateVehicleBrand']);

    Route::post('v1/update-vehicle-model/', [VehicleController::class, 'updateVehicleModel']);

    Route::get('v1/Vehicle-category/', [VehicleController::class, 'getVehicleCategoryData']);

    Route::post('v1/update-Vehicle-category/', [VehicleController::class, 'updateVehicleType']);

    Route::get('v1/vehicle-get/', [VehicleController::class, 'getVehicleData']);



    Route::post('v1/user-note/', [UserNoteController::class, 'register']);

    Route::post('v1/note/', [NoteController::class, 'register']);

    Route::post('v1/car-service/', [CarServiceBookController::class, 'register']);

    Route::post('v1/requete-register/', [RequeteRegisterController::class, 'register']);

    Route::post('v1/amount/', [AddAmountController::class, 'register']);

    Route::get('v1/otp_verify/', [OtpVerificationController::class, 'VerifyOTP']);

    Route::get('v1/otp/', [generateotpController::class, 'OTP']);



    Route::post('v1/forgot-personal-iteam/', [ForgotPersonalIteamController::class, 'register']);

    Route::post('v1/favorite-ride/', [FavoriteRideController::class, 'register']);

    Route::post('v1/onride-requete/', [OnrideRequeteController::class, 'register']);

    Route::post('v1/onride-requete-book/', [OnrideRequeteBookController::class, 'register']);

    Route::post('v1/set-Location/', [SetLocationController::class, 'register']);

    Route::post('v1/canceled-location/', [canceledLocationController::class, 'delete']);

    Route::get('v1/cancel/', [cancelController::class, 'cancel']);

    Route::post('v1/cancel-requete/', [CancelRequeteController::class, 'cancel']);

    Route::get('v1/cancel-requete-book/', [CancelRequeteBookController::class, 'cancel']);

    Route::get('v1/cancel-requete-booking/', [CancelRequeteBookingController::class, 'cancel']);

    Route::get('v1/delete-favorite-ride/', [DeleteFavoriteRideController::class, 'deleteFavoriteRide']);

    Route::get('v1/favorite/', [FavoriteRequeteUserController::class, 'getData']);

    Route::get('v1/location/', [LocationController::class, 'getData']);

    Route::get('v1/notification/', [NotificationListController::class, 'getData']);

    Route::get('v1/payment-method/', [PaymentMethodController::class, 'getData']);

    Route::get('v1/requete/', [RequeteController::class, 'getData']);

    Route::get('v1/requete-book/', [RequeteBookController::class, 'getData']);

    Route::get('v1/requete-book-cancel-user/', [RequeteBookCancelUserController::class, 'getData']);

    Route::get('v1/wallet/', [WalletController::class, 'getData']);

    Route::get('v1/user-gender/', [UseGenderController::class, 'getData']);

    Route::get('v1/transaction/', [TransactionController::class, 'getData']);

    Route::get('v1/taxi/', [taxiController::class, 'getData']);



    Route::get('v1/user-ride/', [RequeteUserappOnRideController::class, 'getData']);

    Route::get('v1/user-confirmation/', [RequeteUserappConfirmationController::class, 'getData']);

    Route::get('v1/user-complete/', [RequeteUserappCompleteController::class, 'getData']);

    Route::get('v1/user-cancel/', [RequeteUserappCanceledController::class, 'getData']);

    Route::get('v1/user-delete/', [DeleteUserController::class, 'deleteuser']);

    Route::get('v1/requete-userapp/', [RequeteUserappController::class, 'getData']);

    Route::get('v1/requete-reject/', [RequeteRejectController::class, 'getData']);

    Route::get('v1/requete-onride/', [RequeteOnrideController::class, 'getData']);

    Route::get('v1/requete-confirm/', [RequeteConfirmController::class, 'getData']);

    Route::get('v1/requete-complete/', [RequeteCompleteController::class, 'getData']);

    Route::get('v1/requete-book-userapp/', [RequeteBookUserappController::class, 'getData']);

    Route::get('v1/requete-book-confirm/', [RequeteBookConfirmController::class, 'getData']);

    Route::get('v1/requete-book-rejected/', [RequeteBookRejectedController::class, 'getData']);

    Route::get('v1/get-ride-review/', [RideDetailsController::class, 'getRideReview']);

    Route::get('v1/user-all-rides/', [RideDetailsController::class, 'getUserRides']);

    Route::get('v1/driver-all-rides/', [RideDetailsController::class, 'getDriverRides']);



    Route::get('v1/driver/', [DriverController::class, 'getData']);

    Route::get('v1/dashboard/', [DashboardController::class, 'getData']);

    Route::get('v1/discount-list/', [DiscountController::class, 'discountList']);

    Route::get('v1/car-service-book/', [CarServiceBookHistoryController::class, 'getData']);

    Route::get('v1/car-images-driver-name/', [CarImagesDriversNameController::class, 'getData']);

    Route::get('v1/driver-review/', [DriverReviewController::class, 'getData']);

    Route::get('v1/driver-review-ride/', [DriverRideReviewController::class, 'getRideReview']);



    Route::get('v1/vehicle-driver/', [DriversVehicleController::class, 'getData']);

    Route::post('v1/fcm-token/', [getFcmController::class, 'getData']);

    Route::get('v1/payment-settings/', [PaymentSettingController::class, 'getData']);

    Route::post('v1/model/', [ModelListController::class, 'getData']);



    Route::post('v1/add-bank-details/', [BankAccountDetailsController::class, 'register']);

    Route::get('v1/bank-details/', [BankAccountDetailsController::class, 'getData']);

    Route::post('v1/withdrawals/', [DriverWithdrawalsController::class, 'Withdrawals']);

    Route::get('v1/withdrawals-list/', [GetDriverWithdrawalsController::class, 'WithdrawalsList']);

    Route::get('v1/ridedetails/', [RideDetailsController::class, 'ridedetails']);

    Route::post('v1/update-user-mdp/', [UsermdpController::class, 'UpdateUsermdp']);

    Route::post('v1/update-user-email/', [UserEmailController::class, 'UpdateUserEmail']);

    Route::post('v1/update-user-roadworthy/', [UserRoadWorthyDocController::class, 'updateRoadWorthy']);

    Route::post('v1/update-user-photo/', [UserPhotoController::class, 'updateUserPhoto']);

    Route::post('v1/update-user-licence/', [UserLicenceController::class, 'updateUserLicence']);

    Route::post('v1/update-position/', [PositionController::class, 'updatePosition']);

    Route::post('v1/feel-safe/', [ReqFeelSafeController::class, 'UpdateReq']);



    Route::post('v1/notify/', [NotifyController::class, 'UpdateNotify']);

    Route::post('v1/driver-confirm/', [CarDriverConfirmController::class, 'confirm']);

    Route::post('v1/change-status/', [ChangeStatusControlller::class, 'changeStatus']);

    Route::get('v1/driver-dashboard-stats/', [DriverDashboardStatsController::class, 'stats']);

    Route::post('v1/complete-requete/', [CompleteRequeteController::class, 'completeRequest']);

    Route::post('v1/confirm-requete/', [ConfirmRequeteController::class, 'confirmRequest']);

    Route::post('v1/contact-us/', [ContactUsController::class, 'contact']);

    Route::post('v1/pay-requete-wallet/', [PayRequeteWalletController::class, 'UpdatePayRequeteWallet']);

    Route::post('v1/payment-by-cash/', [PaymentByCashController::class, 'UpdatePayment']);

    Route::post('v1/user-pending-payment/', [UserPendingPaymentController::class, 'userpayment']);



    Route::post('v1/update-fcm/', [UpdatefcmController::class, 'updatefcm']);

    Route::post('v1/user-address/', [UserAddressController::class, 'UpdateUserAddress']);

    Route::post('v1/set-rejected-requete/', [SetRejectedRequeteController::class, 'rejectedRequest']);

    Route::post('v1/dispatch/check-timeout/', [DriverDispatchController::class, 'checkTimeout']);

    Route::post('v1/dispatch/retry/', [DriverDispatchController::class, 'retryDispatch']);

    Route::post('v1/user-name/', [UserNameController::class, 'UpdateUserName']);

    Route::post('v1/user-pre-name/', [UserPreNameController::class, 'UpdateUserPreName']);

    Route::post('v1/user-phone/', [UserPhoneController::class, 'UpdateUserPhone']);

    Route::post('v1/user-alternate-phone/', [UserPhoneController::class, 'UpdateUserAlternatePhone']);

    Route::post('v1/user-toggle-marketplace/', [UserPhoneController::class, 'ToggleMarketplace']);

    Route::post('v1/not-feel-safe/', [ReqNotFeelSafeController::class, 'UpdateReq']);

    Route::post('v1/resert-password/', [ResertPasswordController::class, 'resertPassword']);

    Route::post('v1/reset-password-otp/', [SendResetPasswordOtpController::class, 'resetPasswordOtp']);

    Route::post('v1/change-status-payment/', [ChangeStatusForpaymentController::class, 'ChangeStatus']);

    Route::get('v1/requete-book-cancel/', [RequeteBookCancelController::class, 'getData']);

    Route::post('v1/storesos/', [SosController::class, 'storeSos']);

    Route::post('v1/update-user-carservice/', [SetCarServiceBookController::class, 'updateCarServiceBook']);



    /*Payments*/

    Route::post('v1/payments/getpaytmchecksum', [PaymentController::class, 'getPaytmChecksum']);

    Route::post('v1/payments/validatechecksum', [PaymentController::class, 'validateChecksum']);

    Route::post('v1/payments/initiatepaytmpayment', [PaymentController::class, 'initiatePaytmPayment']);

    Route::post('v1/payments/paytmpaymentcallback', [PaymentController::class, 'paytmPaymentcallback']);

    Route::post('v1/payments/paypalclientid', [PaymentController::class, 'getPaypalClienttoken']);

    Route::post('v1/payments/paypaltransaction', [PaymentController::class, 'createBraintreePayment']);

    Route::post('v1/payments/stripepaymentintent', [PaymentController::class, 'createStripePaymentIntent']);

    Route::post('v1/payments/razorpay/createorder', [RazorPayController::class, 'createOrderid']);

    Route::post('v1/pay-requete/', [PayRequeteController::class, 'UpdatePayRequete']);

    Route::post('v1/complaints/', [AddComplaintsController::class, 'register']);

    Route::get('v1/complaintsList/', [AddComplaintsController::class, 'index']);



    Route::get('v1/get-referral/', [GetUserReferralCode::class, 'getData']);

    Route::get('v1/get-parcel-category/', [ParcelCategoryController::class, 'getData']);



    Route::get('v1/search-driver-parcel-order/', [SearchDriverParcelOrdersController::class, 'getData']);

    Route::post('v1/parcel-register', [ParcelRegisterController::class, 'register']);

    Route::post('v1/parcel-confirm', [ParcelConfirmController::class, 'confirmRequest']);

    Route::post('v1/parcel-onride', [ParcelOnRideController::class, 'onrideRequest']);

    Route::post('v1/parcel-complete', [ParcelCompleteController::class, 'completeRequest']);

    Route::post('v1/parcel-rejected', [ParcelRejectController::class, 'rejectRequest']);

    Route::post('v1/parcel-canceled', [ParcelCanceledController::class, 'cancelRequest']);

    Route::get('v1/get-driver-parcel-orders', [GetParcelOrdersController::class, 'getDriverParcel']);

    Route::get('v1/get-user-parcel-orders', [GetParcelOrdersController::class, 'getUserParcel']);

    Route::get('v1/get-parcel-detail', [GetParcelOrdersController::class, 'getParcelDetail']);

    Route::post('v1/parcel-pay-requete-wallet/', [PayParcelWalletController::class, 'UpdatePayRequeteWallet']);

    Route::post('v1/parcel-payment-by-cash/', [PaymentByCashParcelController::class, 'UpdatePayment']);

    Route::post('v1/parcel-payment-requete/', [PayParcelRequestController::class, 'UpdatePayment']);



    Route::post('v1/zone-update/', [ZoneController::class, 'updateZone']);

    Route::get('v1/get-banners/', [BannersController::class, 'getData']);

    Route::post('v1/update-user-profile/', [UserProfileUpdateController::class, 'update']);


    Route::get('v1/get-subscription-plans/', [SubscriptionPlanController::class, 'getPlanList']);
    Route::post('v1/set-subscription/', [SubscriptionPlanController::class, 'setData']);
    Route::post('v1/get-subscription-history/', [SubscriptionPlanController::class, 'getSubscriptionHistory']);
    
    // Consumer Plans for User App
    Route::get('v1/get-consumer-plans/', [SubscriptionPlanController::class, 'getConsumerPlans']);
    Route::post('v1/set-consumer-subscription/', [SubscriptionPlanController::class, 'setConsumerSubscription']);

});



Route::get('v1/wallet-history/', [DriverWalletHistoryController::class, 'getData']);



//not found

Route::get('v1/requete-book-confirm-user/', [RequeteBookConfirmUserController::class, 'getData']);

Route::get('v1/changestatuspayment/', [UserController::class, 'test']);

Route::post('v1/user-login-ag/', [User_LoginController::class, 'login']);

Route::get('v1/payfast/', [PayFastController::class, 'getData']);

Route::post('v1/user-photo/', [OldUserPhotoController::class, 'UpdateUserPhoto']);

Route::post('v1/confirm-requete-book/', [ConfirmedRequeteBookController::class, 'confirmRequest']);
//not found end




// Start APi Here 
// Created by Mohd Khushnasib 2025-08-29
Route::post('v1/withdrawWallet/smart-value', [UserProfileUpdateController::class, 'withdrawWallet']);  // withdrawWallet
Route::post('v1/transfer_to_wallet/smart-value', [UserProfileUpdateController::class, 'transfer_to_wallet']);  // transfer_to_wallet manual transfer 
Route::post('v1/adduser/smart-value', [UserProfileUpdateController::class, 'addUser']);          // add user 
Route::any('v1/showadduser/smart-value', [UserProfileUpdateController::class, 'showadduser']);  // show add user 
Route::post('v1/user_changepasswordset/smart-value', [UserProfileUpdateController::class, 'UpdateMpin']); // Change MPIN like (change password)
Route::post('v1/get_profile/smart-value', [UserProfileUpdateController::class, 'GetProfileByAcNo']); // GetProfileByAcNo
Route::post('v1/show_wallet_amount/smart-value', [UserProfileUpdateController::class, 'show_wallet_amount']); 
Route::post('v1/show_transaction_history/smart-value', [UserProfileUpdateController::class, 'show_transaction_history']);  // show_transaction_history
Route::post('v1/show_reward_history/smart-value', [UserProfileUpdateController::class, 'show_reward_history']);  // Reward and cashback 
// CronJob jis din transaction kiya jayega usi din transaction_history se amount nikalke users table me start_date2 and end_date2 ke beech per_sender ka percentage tbl_earning me hit hoga  
Route::any('v1/cronjob_daily/smart-value', [UserProfileUpdateController::class, 'CronjobDaily']);
// Created By Mohd Khushnasib 2025-09-01  perday 24 hour me one time hit api 
Route::get('v1/schedule_3rd/smart-value', [UserProfileUpdateController::class, 'Schedule_3rd']);








################## for driver api same logic user 2025-09-21 ########################
Route::post('v1/adduser/driver/', [DriverWithdrawalsController::class, 'add_user']);
Route::any('v1/showadduser/driver/', [DriverWithdrawalsController::class, 'showadduser']);
Route::post('v1/user_changepasswordset/driver/', [UserProfileUpdateController::class, 'UpdateMpin']); // MPIN
Route::post('v1/user_changepasswordset/driver', [UserProfileUpdateController::class, 'UpdateMpin']); // MPIN
Route::post('v1/GetNameByAcNo/driver/', [DriverWithdrawalsController::class, 'GetNameByAcNo']); // GetNameByAcNo
Route::post('v1/show_wallet_amount/driver', [UserProfileUpdateController::class, 'show_wallet_amount']); 
Route::post('v1/withdrawWallet/driver/', [DriverWithdrawalsController::class, 'withdrawWallet']);  // withdrawWallet

Route::post('v1/book-service', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'bookService']);
Route::get('v1/service-history', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getHistory']);
Route::get('v1/service-booking/{id}', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getBookingDetail']);
Route::get('v1/service-price-estimate', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getServicePriceEstimate']);
Route::post('v1/service-booking/pay', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'payServiceBooking']);
Route::post('v1/service-booking/cancel', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'cancelServiceBooking']);
Route::get('v1/home-services', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getHomeServices']);
Route::get('v1/service-categories', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getServiceCategories']);
Route::get('v1/driver/bookings', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getDriverBookings']);
Route::post('v1/driver/bookings/service-status', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'updateServiceBookingStatus']);
Route::get('v1/driver/wallet-status', [\App\Http\Controllers\API\v1\ServiceRequestAPIController::class, 'getDriverWalletStatus']);

// ── Dynamic Public API Keys Endpoint ──────────────────────────────────────
Route::get('v1/app-settings/keys', function() {
    $keys = \App\Models\ApiKeySetting::where('is_active', true)->get()->mapWithKeys(function($item) {
        return [$item->key_name => $item->key_value];
    });
    return response()->json(['success' => true, 'data' => $keys]);
});

// ── Referral Dashboard API ──────────────────────────────────────────────────
Route::match(['get', 'post'], 'v1/referral-dashboard-stats', [\App\Http\Controllers\API\v1\ReferralDashboardAPIController::class, 'getStats']);
Route::match(['get', 'post'], 'referral-dashboard-stats', [\App\Http\Controllers\API\v1\ReferralDashboardAPIController::class, 'getStats']);
Route::match(['get', 'post'], 'api/v1/referral-dashboard-stats', [\App\Http\Controllers\API\v1\ReferralDashboardAPIController::class, 'getStats']);
Route::get('v1/driver-aadhaar-check', function(\Illuminate\Http\Request $request) {
    $driverId = $request->get('driver_id') ?: $request->get('id_driver') ?: $request->get('user_id') ?: $request->get('id_user');
    $phone = $request->get('phone') ?: $request->get('mobile');
    $email = $request->get('email');
    $userCat = $request->get('user_cat', '');

    $aadhar = '';
    $driverRow = null;
    $userRow = null;

    // 1. Check by ID
    if ($driverId && $driverId != '0') {
        if ($userCat === 'customer' || $userCat === 'user_app') {
            $userRow = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $driverId)->first();
        } elseif ($userCat === 'driver') {
            $driverRow = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driverId)->first();
        }
        if (!$driverRow && !$userRow) {
            $userRow = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $driverId)->first();
            $driverRow = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driverId)->first();
        }
    }

    // 2. Check by phone number
    if (!$driverRow && !$userRow && !empty($phone)) {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
        if (strlen($cleanPhone) >= 10) {
            $last10 = substr($cleanPhone, -10);
            $userRow = \Illuminate\Support\Facades\DB::table('tj_user_app')
                ->where('phone', 'like', "%{$last10}")
                ->first();

            $driverRow = \Illuminate\Support\Facades\DB::table('tj_conducteur')
                ->where('phone', 'like', "%{$last10}")
                ->first();
        }
    }

    // 3. Check by email
    if (!$driverRow && !$userRow && !empty($email)) {
        $userRow = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('email', $email)->first();
        $driverRow = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('email', $email)->first();
    }

    if ($userRow && !empty($userRow->aadhar_number)) {
        $aadhar = trim((string)$userRow->aadhar_number);
    }
    if (empty($aadhar) && $driverRow && !empty($driverRow->aadhar_number)) {
        $aadhar = trim((string)$driverRow->aadhar_number);
    }

    $cleanAadhar = preg_replace('/[^0-9]/', '', $aadhar);
    $hasSubmitted = (strlen($cleanAadhar) === 12);

    return response()->json([
        'success' => true,
        'aadhar_submitted' => $hasSubmitted,
        'aadhar_number' => $hasSubmitted ? $cleanAadhar : '',
    ]);
});

Route::post('v1/user/submit-aadhar', function(\Illuminate\Http\Request $request) {
    $json = [];
    try {
        $raw = $request->getContent();
        if (!empty($raw)) {
            $json = json_decode($raw, true) ?: [];
        }
    } catch (\Throwable $e) {}

    $userId = $request->input('user_id') ?: ($json['user_id'] ?? ($request->input('id_user') ?: ($json['id_user'] ?? ($request->get('user_id') ?: $request->get('id_user')))));
    $driverId = $request->input('driver_id') ?: ($json['driver_id'] ?? ($request->input('id_conducteur') ?: ($json['id_conducteur'] ?? ($request->get('driver_id') ?: $request->get('id_conducteur')))));
    $phone = $request->input('phone') ?: ($json['phone'] ?? ($request->input('mobile') ?: ($json['mobile'] ?? ($request->get('phone') ?: $request->get('mobile')))));
    $userCat = strtolower(trim((string)($request->input('user_cat') ?: ($json['user_cat'] ?? ($request->input('user_type') ?: ($json['user_type'] ?? ($request->get('user_cat') ?: $request->get('user_type'))))))));
    $rawAadhar = $request->input('aadhar_number') ?: ($json['aadhar_number'] ?? ($request->get('aadhar_number') ?? ''));
    $aadharNumber = preg_replace('/[^0-9]/', '', (string)$rawAadhar);

    if (strlen($aadharNumber) !== 12) {
        return response()->json([
            'success' => false,
            'message' => 'Please enter a valid 12-digit Aadhaar number.'
        ], 400);
    }

    // Determine current user's registered phone
    $currentUserPhone = '';
    if (($userCat === 'driver' || empty($userCat)) && $driverId) {
        $currentUserPhone = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driverId)->value('phone') ?? '';
    }
    if (empty($currentUserPhone) && $userId) {
        $currentUserPhone = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $userId)->value('phone') ?? '';
    }
    if (empty($currentUserPhone) && !empty($phone)) {
        $currentUserPhone = $phone;
    }
    $cleanCurrentPhone = preg_replace('/[^0-9]/', '', (string)$currentUserPhone);
    $currentLast10 = strlen($cleanCurrentPhone) >= 10 ? substr($cleanCurrentPhone, -10) : '';

    // Check same number logic: Is this Aadhaar already linked to a different phone number?
    if (!empty($currentLast10)) {
        $otherUser = \Illuminate\Support\Facades\DB::table('tj_user_app')
            ->where('aadhar_number', $aadharNumber)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('phone', 'not like', "%{$currentLast10}")
            ->first();

        $otherDriver = \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->where('aadhar_number', $aadharNumber)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('phone', 'not like', "%{$currentLast10}")
            ->first();

        if ($otherUser || $otherDriver) {
            return response()->json([
                'success' => false,
                'res' => 'error',
                'message' => 'This Aadhaar card number is already registered with a different mobile number.'
            ], 422);
        }
    }

    $updated = false;
    if (($userCat === 'customer' || $userCat === 'user_app' || empty($userCat)) && $userId && \Illuminate\Support\Facades\Schema::hasTable('tj_user_app')) {
        \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $userId)->update(['aadhar_number' => $aadharNumber]);
        $updated = true;
    }
    if (($userCat === 'driver' || empty($userCat)) && $driverId && \Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
        \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driverId)->update(['aadhar_number' => $aadharNumber]);
        $updated = true;
    }
    if (!$updated && !empty($currentLast10)) {
        if ($userCat === 'driver') {
            \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('phone', 'like', "%{$currentLast10}")->update(['aadhar_number' => $aadharNumber]);
        } else {
            \Illuminate\Support\Facades\DB::table('tj_user_app')->where('phone', 'like', "%{$currentLast10}")->update(['aadhar_number' => $aadharNumber]);
        }
        $updated = true;
    }

    return response()->json([
        'success' => true,
        'res' => 'success',
        'message' => 'Aadhaar registered & verified successfully!'
    ]);
});

// ── Medical Cashback API Endpoints ──────────────────────────────────────────
Route::get('v1/medical-cashback/plans', [MedicalCashbackController::class, 'getPlans']);
Route::get('v1/medical-cashback/payment-settings', [MedicalCashbackController::class, 'getPaymentSettings']);
Route::post('v1/medical-cashback/purchase-card', [MedicalCashbackController::class, 'purchaseCard']);
Route::get('v1/medical-cashback/my-card', [MedicalCashbackController::class, 'getMyCard']);
Route::post('v1/medical-cashback/submit-claim', [MedicalCashbackController::class, 'submitClaim']);
Route::post('v1/medical-cashback/reupload-claim/{claimId}', [MedicalCashbackController::class, 'reuploadClaim']);

// ── Customer Support Chat API Endpoints ─────────────────────────────────────
Route::get('v1/support/quick-questions', [\App\Http\Controllers\API\SupportChatApiController::class, 'getQuickQuestions']);
Route::post('v1/support/ticket', [\App\Http\Controllers\API\SupportChatApiController::class, 'getOrCreateTicket']);
Route::get('v1/support/messages', [\App\Http\Controllers\API\SupportChatApiController::class, 'getMessages']);
Route::post('v1/support/send-message', [\App\Http\Controllers\API\SupportChatApiController::class, 'sendMessage']);
Route::post('v1/support/close-ticket', [\App\Http\Controllers\API\SupportChatApiController::class, 'closeTicket']);

// ── App Version Control & Upgrade API ───────────────────────────────────────
Route::get('v1/app-version/check', [\App\Http\Controllers\API\AppVersionApiController::class, 'checkVersion']);

// ── Driver & Partner Welcome Kit API ────────────────────────────────────────
Route::get('v1/driver/kit-status', [\App\Http\Controllers\API\DriverKitApiController::class, 'getKitStatus']);
Route::post('v1/driver/kit-purchase/record', [\App\Http\Controllers\API\DriverKitApiController::class, 'recordPurchase']);









