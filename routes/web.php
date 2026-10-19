<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SecureDownloadController;

// Auth routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);

// Email verification routes
Route::get('/verify', [App\Http\Controllers\AuthController::class, 'showVerifyForm'])->name('verify');
Route::post('/verify', [App\Http\Controllers\AuthController::class, 'verify']);
Route::post('/verify/resend', [App\Http\Controllers\AuthController::class, 'resendOtp'])->name('verify.resend');

// Password reset routes
Route::get('/forgot-password', [App\Http\Controllers\AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/reset-password', [App\Http\Controllers\AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword'])->name('password.update');

// User Dashboard Routes (Protected by member middleware)
Route::middleware(['member'])->prefix('user')->name('user.')->group(function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/history', [App\Http\Controllers\UserController::class, 'orderHistory'])->name('history');
    Route::get('/order/{orderNumber}', [App\Http\Controllers\UserController::class, 'orderDetail'])->name('order.detail');
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');
});

// Homepage Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Product Routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/category/{category}', [ProductController::class, 'byCategory'])->name('products.category');
});

// Individual Product Route (singular)
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// API Routes for AJAX calls
Route::prefix('api')->group(function () {
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/popular', [ProductController::class, 'popular']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/by-category', [ProductController::class, 'groupedByCategory']);
    Route::get('/payment-methods', [HomeController::class, 'paymentMethods']);
    Route::get('/work-hours', [HomeController::class, 'workHours']);
    
    // Invoice status check
    Route::get('/invoice/{orderNumber}/status', [InvoiceController::class, 'status'])->name('invoice.status');
    
    // Midtrans API routes (removed - these are handled at the bottom)
});

// News Routes
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('news.index');
    Route::get('/{article:slug}', [NewsController::class, 'show'])->name('news.show');
});

// FAQ Routes
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');

// Payment Routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/{orderNumber}', [PaymentController::class, 'show'])->name('show');
    Route::get('/{orderNumber}/status', [PaymentController::class, 'checkStatus'])->name('status'); 
    Route::get('/{orderNumber}/callback-status', [App\Http\Controllers\PaymentWebhookController::class, 'checkCallbackStatus'])->name('callback-status');
    Route::get('/{orderNumber}/stream', [App\Http\Controllers\PaymentWebhookController::class, 'streamPaymentStatus'])->name('stream');
    Route::get('/{orderNumber}/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/{orderNumber}/failed', [PaymentController::class, 'failed'])->name('failed');
});

// Invoice Routes
//Route::get('/invoice/{orderNumber}', [InvoiceController::class, 'show'])->name('invoice.show');

// Invoice routes
Route::prefix('invoice')->name('invoice.')->group(function () {
    Route::get('/{orderNumber}', [InvoiceController::class, 'show'])->name('show');
    Route::get('/{orderNumber}/refresh-deliveries', [InvoiceController::class, 'refreshDigitalDeliveries'])->name('refresh-deliveries');
});

// Midtrans Payment Routes
Route::prefix('midtrans')->name('midtrans.')->group(function () {
    // Callback dari Midtrans server
    Route::post('/notification', [MidtransController::class, 'notification'])->name('notification');
    // Redirect setelah payment (success/error/pending)
    Route::get('/finish', [MidtransController::class, 'finish'])->name('finish');
    Route::get('/unfinish', [MidtransController::class, 'unfinish'])->name('unfinish');
    Route::get('/error', [MidtransController::class, 'error'])->name('error');
});

// Dynamic Page Routes
Route::get('/{page:slug}', [PageController::class, 'show'])->name('pages.show');

// Digital download routes (tidak perlu auth untuk guest)
Route::prefix('digital')->name('digital.')->group(function () {
    // Secure download dengan token
    Route::get('/download/{token}', [SecureDownloadController::class, 'download'])
         ->name('download');
    
    // Check download status (untuk AJAX)
    Route::get('/status/{token}', [SecureDownloadController::class, 'checkStatus'])
         ->name('status');
});
