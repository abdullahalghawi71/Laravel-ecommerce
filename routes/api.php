<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderProcessController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['jwt.verify'])->group(function () {
    Route::middleware(['jwt.auth'])->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('locations', LocationController::class);
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('order_items', OrderItemController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('inventories', InventoryController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('issue',IssueController::class);
        Route::apiResource('report',ReportController::class);
        Route::get('productByCategory/{id}', [ProductController::class, 'GetProductByCategory']);

        Route::get('product-suppliers', [ProductSupplierController::class, 'index']);
        Route::get('product-suppliers/{product_id}/{supplier_id}', [ProductSupplierController::class, 'show']);
        Route::post('product-suppliers', [ProductSupplierController::class, 'store']);
        Route::put('product-suppliers/{product_id}/{supplier_id}', [ProductSupplierController::class, 'update']);
        Route::delete('product-suppliers/{product_id}/{supplier_id}', [ProductSupplierController::class, 'destroy']);

        Route::post('make_product', [OrderProcessController::class, 'store']);
        Route::get('statistics',[DashboardController::class, 'statistics']);


        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::post('/send-reset-code', [ResetPasswordController::class, 'sendResetCode']);
Route::post('/password/verify-code', [ResetPasswordController::class, 'verifyCode']);
Route::post('/password/reset', [ResetPasswordController::class, 'resetPassword']);

Route::get('featured_products',[ProductSupplierController::class,'featured_products']);

