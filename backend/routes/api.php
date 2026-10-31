<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use Illuminate\Support\Facades\Route;

// 1. PUBLIC ROUTES
Route::post('/login', [AuthController::class, 'login']);

// 2. PROTECTED ROUTES
Route::middleware('auth.api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Modul POS Kasir
    Route::prefix('pos')->group(function () {
        Route::get('/menu', [PosController::class, 'getMenu']);
        Route::post('/checkout', [PosController::class, 'checkout']);
        Route::get('/invoice/{id}', [PosController::class, 'getInvoice']);
        Route::get('/history', [PosController::class, 'getHistory']);
    });

    // Modul Produk & Inventaris
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/categories', [ProductController::class, 'getCategories']);
        Route::post('/', [ProductController::class, 'store'])->middleware('role:admin');
        Route::put('/{id}', [ProductController::class, 'update'])->middleware('role:admin');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('role:admin');
        Route::post('/{id}/stock-adjustment', [ProductController::class, 'adjustStock'])->middleware('role:admin');
    });

    // Modul Pengadaan
    Route::prefix('purchases')->middleware('role:admin,pemilik')->group(function () {
        Route::get('/', [PurchaseController::class, 'index']);
        Route::post('/', [PurchaseController::class, 'store']);
        Route::get('/suppliers', [PurchaseController::class, 'getSuppliers']);
    });

    // Modul Biaya Operasional
    Route::prefix('expenses')->middleware('role:admin,pemilik')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::delete('{id}', [ExpenseController::class, 'destroy']);
    });

    // Modul Analitik & Dashboard (Terbuka untuk SEMUA role, termasuk Kasir)
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'getDashboard']);
        Route::get('/profit-loss', [AnalyticsController::class, 'profitLoss'])->middleware('role:admin,pemilik');
    });
});
