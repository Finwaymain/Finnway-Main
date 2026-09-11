<?php

use App\Http\Controllers\API\v1\Food\CustomerFoodController;
use App\Http\Controllers\API\v1\Food\RestaurantAuthController;
use App\Http\Controllers\API\v1\Food\RestaurantFinanceController;
use App\Http\Controllers\API\v1\Food\RestaurantMenuController;
use App\Http\Controllers\API\v1\Food\RestaurantOnboardingController;
use App\Http\Controllers\API\v1\Food\RestaurantOrderController;
use App\Http\Controllers\API\v1\Food\RiderFoodController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['envKeyAuth'], 'prefix' => 'v1/food'], function () {
    // Auth (public within env key)
    Route::post('auth/check-user', [RestaurantAuthController::class, 'checkUser']);
    Route::post('auth/send-otp', [RestaurantAuthController::class, 'sendOtp']);
    Route::post('auth/verify-otp', [RestaurantAuthController::class, 'verifyOtp']);
    Route::post('auth/login-mpin', [RestaurantAuthController::class, 'loginMpin']);
    Route::post('auth/setup-mpin', [RestaurantAuthController::class, 'setupMpin']);
    Route::post('auth/login-password', [RestaurantAuthController::class, 'loginPassword']);
    Route::get('types', [RestaurantAuthController::class, 'types']);

    // Customer discovery / order (user token optional for MVP; phone/id in body)
    Route::get('customer/nearby', [CustomerFoodController::class, 'nearby']);
    Route::get('customer/restaurants/{id}/menu', [CustomerFoodController::class, 'restaurantMenu']);
    Route::post('customer/orders', [CustomerFoodController::class, 'placeOrder']);
    Route::get('customer/orders', [CustomerFoodController::class, 'myOrders']);
    Route::get('customer/orders/{id}', [CustomerFoodController::class, 'track']);
    Route::post('customer/orders/{id}/rate', [CustomerFoodController::class, 'rate']);
    Route::get('customer/orders/{id}/reorder', [CustomerFoodController::class, 'reorder']);

    // Rider food delivery
    Route::get('rider/incoming', [RiderFoodController::class, 'incoming']);
    Route::post('rider/orders/{id}/accept', [RiderFoodController::class, 'accept']);
    Route::get('rider/active', [RiderFoodController::class, 'active']);
    Route::post('rider/orders/{id}/status', [RiderFoodController::class, 'updateStatus']);
    Route::get('rider/dues', [RiderFoodController::class, 'dues']);
    Route::post('rider/dues/pay', [RiderFoodController::class, 'payDue']);

    // Restaurant authenticated
    Route::group(['middleware' => ['restaurant.auth'], 'prefix' => 'restaurant'], function () {
        Route::get('profile', [RestaurantAuthController::class, 'profile']);
        Route::post('profile', [RestaurantAuthController::class, 'updateProfile']);
        Route::post('logout', [RestaurantAuthController::class, 'logout']);

        Route::get('me', [RestaurantOnboardingController::class, 'myRestaurant']);
        Route::post('onboarding', [RestaurantOnboardingController::class, 'submit']);
        Route::post('onboarding/payment/init', [RestaurantOnboardingController::class, 'initiatePayment']);
        Route::post('onboarding/payment/confirm', [RestaurantOnboardingController::class, 'confirmPayment']);
        Route::post('onboarding/payment/proof', [RestaurantOnboardingController::class, 'submitPaymentProof']);
        Route::post('operational-status', [RestaurantOnboardingController::class, 'setOperationalStatus']);
        Route::post('update', [RestaurantOnboardingController::class, 'updateProfile']);

        Route::get('categories', [RestaurantMenuController::class, 'categories']);
        Route::post('categories', [RestaurantMenuController::class, 'saveCategory']);
        Route::post('categories/{id}', [RestaurantMenuController::class, 'saveCategory']);
        Route::delete('categories/{id}', [RestaurantMenuController::class, 'deleteCategory']);
        Route::get('products', [RestaurantMenuController::class, 'products']);
        Route::post('products', [RestaurantMenuController::class, 'saveProduct']);
        Route::post('products/{id}', [RestaurantMenuController::class, 'saveProduct']);
        Route::delete('products/{id}', [RestaurantMenuController::class, 'deleteProduct']);
        Route::post('products/{id}/availability', [RestaurantMenuController::class, 'toggleAvailability']);

        Route::get('dashboard', [RestaurantOrderController::class, 'dashboard']);
        Route::get('orders/incoming', [RestaurantOrderController::class, 'incoming']);
        Route::get('orders', [RestaurantOrderController::class, 'list']);
        Route::get('orders/{id}', [RestaurantOrderController::class, 'show']);
        Route::post('orders/{id}/accept', [RestaurantOrderController::class, 'accept']);
        Route::post('orders/{id}/reject', [RestaurantOrderController::class, 'reject']);
        Route::post('orders/{id}/status', [RestaurantOrderController::class, 'updateStatus']);
        Route::post('orders/{id}/handover', [RestaurantOrderController::class, 'confirmHandover']);

        Route::get('sales', [RestaurantFinanceController::class, 'sales']);
        Route::get('dues', [RestaurantFinanceController::class, 'dues']);
        Route::post('dues/pay', [RestaurantFinanceController::class, 'payDue']);
        Route::get('settlements', [RestaurantFinanceController::class, 'settlements']);
        Route::get('transactions', [RestaurantFinanceController::class, 'transactions']);
        Route::get('offers', [RestaurantFinanceController::class, 'offers']);
        Route::post('offers', [RestaurantFinanceController::class, 'saveOffer']);
        Route::post('offers/{id}', [RestaurantFinanceController::class, 'saveOffer']);
        Route::get('reviews', [RestaurantFinanceController::class, 'reviews']);
        Route::post('reviews/{id}/reply', [RestaurantFinanceController::class, 'replyReview']);
        Route::get('disputes', [RestaurantFinanceController::class, 'disputes']);
        Route::post('disputes', [RestaurantFinanceController::class, 'raiseDispute']);
        Route::get('support', [RestaurantFinanceController::class, 'tickets']);
        Route::post('support', [RestaurantFinanceController::class, 'createTicket']);
        Route::get('notifications', [RestaurantFinanceController::class, 'notifications']);
        Route::post('notifications/{id}/read', [RestaurantFinanceController::class, 'markNotificationRead']);
    });
});
