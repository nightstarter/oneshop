<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PriceListController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('price-lists', PriceListController::class);
        Route::apiResource('customers', CustomerController::class);

        // Orders are read-only; status updates via PATCH
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    });
