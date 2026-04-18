<?php

use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\Auth\LoginController;
use App\Http\Controllers\Frontend\Auth\RegisterController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PaymentGatewayController;
use App\Http\Controllers\Frontend\ProductImageViewController;
use App\Http\Controllers\Frontend\ProductCatalogController;
use App\Http\Controllers\AdminWeb\CategoryController as AdminCategoryController;
use App\Http\Controllers\AdminWeb\CustomerController as AdminCustomerController;
use App\Http\Controllers\AdminWeb\DashboardController as AdminDashboardController;
use App\Http\Controllers\AdminWeb\OrderController as AdminOrderController;
use App\Http\Controllers\AdminWeb\ProductCompositionController as AdminProductCompositionController;
use App\Http\Controllers\AdminWeb\ProductImageController as AdminProductImageController;
use App\Http\Controllers\AdminWeb\PaymentTransactionController as AdminPaymentTransactionController;
use App\Http\Controllers\AdminWeb\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\AdminWeb\PriceListController as AdminPriceListController;
use App\Http\Controllers\AdminWeb\ProductController as AdminProductController;
use App\Http\Controllers\AdminWeb\ShippingMethodController as AdminShippingMethodController;
use App\Http\Controllers\AdminWeb\ThemeController as AdminThemeController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Public frontend
// ---------------------------------------------------------------------------

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
Route::get('/category/{category:slug}', [ProductCatalogController::class, 'category'])->name('products.category');
Route::get('/product/{product:slug}', [ProductCatalogController::class, 'show'])->name('products.show');
Route::get('/images/products/placeholder', [ProductImageViewController::class, 'placeholder'])->name('product-images.placeholder');
Route::get('/images/products/{mediaFile}', [ProductImageViewController::class, 'show'])->name('product-images.show');

// ---------------------------------------------------------------------------
// Cart (available to both guest and authenticated users)
// ---------------------------------------------------------------------------

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// ---------------------------------------------------------------------------
// Checkout
// ---------------------------------------------------------------------------

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/payment-methods', [CheckoutController::class, 'paymentMethods'])->name('checkout.payment-methods');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::post('/payments/{provider}/callback', [PaymentGatewayController::class, 'callback'])->name('payments.callback');
Route::get('/payments/{provider}/return', [PaymentGatewayController::class, 'return'])->name('payments.return');

// ---------------------------------------------------------------------------
// Auth
// ---------------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ---------------------------------------------------------------------------
// Customer account (requires authentication)
// ---------------------------------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'showOrder'])->name('account.order');
});

// ---------------------------------------------------------------------------
// Admin web (Blade)
// ---------------------------------------------------------------------------

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::post('products/{product}/composition', [AdminProductCompositionController::class, 'store'])->name('products.composition.store');
    Route::patch('products/{product}/composition/{component}', [AdminProductCompositionController::class, 'update'])->name('products.composition.update');
    Route::delete('products/{product}/composition/{component}', [AdminProductCompositionController::class, 'destroy'])->name('products.composition.destroy');

    Route::post('products/{product}/images', [AdminProductImageController::class, 'store'])->name('products.images.store');
    Route::patch('products/{product}/images/{productImage}', [AdminProductImageController::class, 'update'])->name('products.images.update');
    Route::post('products/{product}/images/{productImage}/main', [AdminProductImageController::class, 'makePrimary'])->name('products.images.main');
    Route::delete('products/{product}/images/{productImage}', [AdminProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('price-lists', AdminPriceListController::class)->except(['show']);
    Route::resource('shipping-methods', AdminShippingMethodController::class)->except(['show']);
    Route::resource('payment-methods', AdminPaymentMethodController::class)->except(['show']);
    Route::resource('customers', AdminCustomerController::class)->except(['show']);
    Route::get('themes', [AdminThemeController::class, 'index'])->name('themes.index');
    Route::put('themes', [AdminThemeController::class, 'update'])->name('themes.update');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('payment-transactions', [AdminPaymentTransactionController::class, 'index'])->name('payment-transactions.index');
    Route::get('payment-transactions/{payment_transaction}', [AdminPaymentTransactionController::class, 'show'])->name('payment-transactions.show');
});

