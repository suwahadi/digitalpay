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
use App\Http\Controllers\OrderAccessController;

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->middleware('throttle:6,1');

Route::get('/verify', [App\Http\Controllers\AuthController::class, 'showVerifyForm'])->name('verify');
Route::post('/verify', [App\Http\Controllers\AuthController::class, 'verify'])->middleware('throttle:6,1');
Route::post('/verify/resend', [App\Http\Controllers\AuthController::class, 'resendOtp'])->middleware('throttle:3,1')->name('verify.resend');

Route::get('/forgot-password', [App\Http\Controllers\AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'forgotPassword'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password', [App\Http\Controllers\AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');

Route::middleware(['member'])->prefix('user')->name('user.')->group(function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/history', [App\Http\Controllers\UserController::class, 'orderHistory'])->name('history');
    Route::get('/order/{orderNumber}', [App\Http\Controllers\UserController::class, 'orderDetail'])->name('order.detail');
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/category/{category:slug}', [ProductController::class, 'byCategory'])->name('products.category');
});

Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::prefix('api')->group(function () {
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/popular', [ProductController::class, 'popular']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/by-category', [ProductController::class, 'groupedByCategory']);
    Route::get('/payment-methods', [HomeController::class, 'paymentMethods']);
    Route::get('/work-hours', [HomeController::class, 'workHours']);
    
    Route::get('/invoice/{orderNumber}/status', [InvoiceController::class, 'status'])->middleware('order.access')->name('invoice.status');
    
});

Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('news.index');
    Route::get('/{article:slug}', [NewsController::class, 'show'])->name('news.show');
});

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');

Route::prefix('payment')->name('payment.')->middleware('order.access')->group(function () {
    Route::get('/{orderNumber}', [PaymentController::class, 'show'])->name('show');
    Route::get('/{orderNumber}/status', [PaymentController::class, 'checkStatus'])->name('status'); 
    Route::get('/{orderNumber}/callback-status', [App\Http\Controllers\PaymentWebhookController::class, 'checkCallbackStatus'])->name('callback-status');
    Route::get('/{orderNumber}/stream', [App\Http\Controllers\PaymentWebhookController::class, 'streamPaymentStatus'])->name('stream');
    Route::get('/{orderNumber}/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/{orderNumber}/failed', [PaymentController::class, 'failed'])->name('failed');
});

Route::get('/order/{orderNumber}/verify', [OrderAccessController::class, 'show'])->name('order.verify');
Route::post('/order/{orderNumber}/verify', [OrderAccessController::class, 'verify'])->name('order.verify.submit');

Route::prefix('invoice')->name('invoice.')->middleware('order.access')->group(function () {
    Route::get('/{orderNumber}', [InvoiceController::class, 'show'])->name('show');
    Route::get('/{orderNumber}/refresh-deliveries', [InvoiceController::class, 'refreshDigitalDeliveries'])->name('refresh-deliveries');
    Route::post('/{orderNumber}/manual-paid', [InvoiceController::class, 'confirmManualPayment'])->middleware('throttle:6,1')->name('manual-paid');
});

Route::prefix('midtrans')->name('midtrans.')->group(function () {
    Route::post('/notification', [MidtransController::class, 'notification'])->name('notification');
    Route::get('/finish', [MidtransController::class, 'finish'])->name('finish');
    Route::get('/unfinish', [MidtransController::class, 'unfinish'])->name('unfinish');
    Route::get('/error', [MidtransController::class, 'error'])->name('error');
});

Route::get('/{page:slug}', [PageController::class, 'show'])->name('pages.show');

Route::prefix('digital')->name('digital.')->group(function () {
    Route::get('/download/{token}', [SecureDownloadController::class, 'download'])
         ->name('download');
    
    Route::get('/status/{token}', [SecureDownloadController::class, 'checkStatus'])
         ->name('status');
});
