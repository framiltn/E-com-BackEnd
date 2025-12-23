<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SellerApplicationController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\RazorpayController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SellerOrderController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\StoreSettingsController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\AffiliateOfferController;
use App\Http\Controllers\Api\CMSController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (NO AUTH REQUIRED)
|--------------------------------------------------------------------------
*/

// Health check endpoints
Route::get('/health', [\App\Http\Controllers\Api\HealthCheckController::class, 'basic']);
Route::get('/health/detailed', [\App\Http\Controllers\Api\HealthCheckController::class, 'detailed']);

// API Info
Route::get('/', [\App\Http\Controllers\Api\ApiInfoController::class, 'index']);

// Public routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Categories (public)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/slug/{slug}', [CategoryController::class, 'bySlug']);

// Product reviews (public)
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Shiprocket Webhook (no auth required)
Route::post('/webhooks/shiprocket', [\App\Http\Controllers\Api\ShiprocketWebhookController::class, 'handle']);

// CMS (public)
Route::get('/banners', [CMSController::class, 'getBanners']);
Route::get('/pages', [CMSController::class, 'getPages']);
Route::get('/pages/{slug}', [CMSController::class, 'getPage']);
Route::get('/faqs', [CMSController::class, 'getFAQs']);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Rate limit: 60 requests per minute
    Route::middleware('throttle:60,1')->group(function () {
        
        // Checkout
        Route::post('/checkout', [CheckoutController::class, 'checkout']);

    // Razorpay
    Route::post('/payment/verify', [RazorpayController::class, 'verifyPayment']);
    Route::post('/payment/create-order', [RazorpayController::class, 'createOrder']);

    // Cart
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'view']);
    Route::post('/cart/update', [CartController::class, 'updateQuantity']);
    Route::post('/cart/remove', [CartController::class, 'remove']);

    // Coupons
    Route::post('/coupons/validate', [CouponController::class, 'validate']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/my', [ReviewController::class, 'myReviews']);

    // Refunds
    Route::get('/refunds', [RefundController::class, 'index']);
    Route::post('/refunds', [RefundController::class, 'store']);
    Route::get('/refunds/{id}', [RefundController::class, 'show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Seller product management
    // Seller product management routes moved to 'seller' prefix group below

    // Seller Application
    Route::post('/seller/apply', [SellerApplicationController::class, 'apply']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Update Profile
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Current user
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $roles = $user->getRoleNames();
        
        if ($roles->contains('admin')) {
            $user->role = 'admin';
        } elseif ($roles->contains('seller')) {
            $user->role = 'seller';
        } else {
            $user->role = 'buyer';
        }
        
        return $user;
    });

    // Affiliate System
    Route::get('/affiliate', [AffiliateController::class, 'index']);
    Route::get('/affiliate/referrals', [AffiliateController::class, 'referrals']);
    Route::get('/affiliate/tree', [AffiliateController::class, 'tree']);

    // Payout System
    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::post('/payouts', [PayoutController::class, 'store']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'add']);
    Route::delete('/wishlist/{productId}', [WishlistController::class, 'remove']);

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::post('/disputes', [DisputeController::class, 'store']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
    Route::post('/disputes/{id}/messages', [DisputeController::class, 'addMessage']);

    // Affiliate Offers
    Route::get('/affiliate/offers', [AffiliateOfferController::class, 'myOffers']);
    Route::get('/affiliate/offers/eligibility', [AffiliateOfferController::class, 'checkEligibility']);
    Route::post('/affiliate/offers/share-coupon', [AffiliateOfferController::class, 'shareCoupon']);

    // Seller Routes
    Route::prefix('seller')->middleware('role:seller')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Api\SellerController::class, 'dashboard']);

        // Product Management
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products', [ProductController::class, 'sellerIndex']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Store Settings
        Route::get('/settings', [StoreSettingsController::class, 'show']);
        Route::put('/settings', [StoreSettingsController::class, 'update']);
        Route::post('/settings/logo', [StoreSettingsController::class, 'uploadLogo']);
        Route::post('/settings/banner', [StoreSettingsController::class, 'uploadBanner']);

        // Seller Orders
        Route::get('/orders', [SellerOrderController::class, 'index']);
        Route::get('/orders/{id}', [SellerOrderController::class, 'show']);
        Route::post('/orders/{id}/status', [SellerOrderController::class, 'updateStatus']);
        Route::get('/analytics', [SellerOrderController::class, 'analytics']);

        // Product Images
        Route::post('/products/{id}/images', [ImageUploadController::class, 'uploadProductImage']);
        Route::delete('/products/{productId}/images/{imageId}', [ImageUploadController::class, 'deleteProductImage']);
        Route::post('/products/{productId}/images/{imageId}/primary', [ImageUploadController::class, 'setPrimaryImage']);

        // Affiliate Offers (Seller)
        Route::post('/offers', [AffiliateOfferController::class, 'createOffer']);
    });

    // Admin Routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Seller Applications
        Route::get('/applications', [AdminController::class, 'pendingApplications']);
        Route::post('/applications/{id}/approve', [AdminController::class, 'approveApplication']);
        Route::post('/applications/{id}/reject', [AdminController::class, 'rejectApplication']);

        // Product Approvals
        Route::get('/products/pending', [AdminController::class, 'pendingProducts']);
        Route::post('/products/{id}/approve', [AdminController::class, 'approveProduct']);
        Route::post('/products/{id}/reject', [AdminController::class, 'rejectProduct']);

        // Disputes
        Route::get('/disputes', [DisputeController::class, 'adminIndex']);
        Route::post('/disputes/{id}/resolve', [DisputeController::class, 'adminResolve']);

        // CMS Management
        Route::post('/banners', [CMSController::class, 'createBanner']);
        Route::put('/banners/{id}', [CMSController::class, 'updateBanner']);
        Route::delete('/banners/{id}', [CMSController::class, 'deleteBanner']);

        Route::post('/pages', [CMSController::class, 'createPage']);
        Route::put('/pages/{id}', [CMSController::class, 'updatePage']);

        Route::post('/faqs', [CMSController::class, 'createFAQ']);
        Route::put('/faqs/{id}', [CMSController::class, 'updateFAQ']);
        Route::delete('/faqs/{id}', [CMSController::class, 'deleteFAQ']);

        // Affiliate Offers (Admin)
        Route::post('/offers', [AffiliateOfferController::class, 'createOffer']);

        // User Management
        Route::get('/users', [AdminController::class, 'users']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

        // Seller Management
        Route::get('/sellers', [AdminController::class, 'sellers']);
        Route::post('/sellers', [AdminController::class, 'createSeller']);
        Route::put('/sellers/{id}', [AdminController::class, 'updateSeller']);
        Route::delete('/sellers/{id}', [AdminController::class, 'deleteSeller']);
    });

    // User Account Deletion
    Route::delete('/user/account', [AuthController::class, 'deleteAccount']);

    }); // End throttle group

});
