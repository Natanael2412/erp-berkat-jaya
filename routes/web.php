<?php

use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->to(auth()->user()->homeRoute()) : view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ===== Owner & Admin Gudang: operasional =====
    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('units', UnitController::class)->only(['index', 'store', 'update']);
        Route::resource('suppliers', SupplierController::class)->except(['show', 'destroy']);
        Route::resource('products', ProductController::class)->except(['destroy']);

        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('purchase-orders.receive.form');
        Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::get('purchase-orders/{purchase_order}/return', [PurchaseOrderController::class, 'returnForm'])->name('purchase-orders.return.form');
        Route::post('purchase-orders/{purchase_order}/return', [PurchaseOrderController::class, 'returnGoods'])->name('purchase-orders.return');
        Route::post('purchase-orders/{purchase_order}/complete', [PurchaseOrderController::class, 'markAsComplete'])->name('purchase-orders.complete');
        Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    });

    // ===== Owner saja: keuangan & akuntansi =====
    Route::middleware('role:owner')->group(function () {
        Route::post('purchase-orders/{purchase_order}/pay', [PurchaseOrderController::class, 'markAsPaid'])->name('purchase-orders.pay');
        Route::post('sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');

        Route::get('cash', [CashTransactionController::class, 'index'])->name('cash.index');
        Route::post('cash', [CashTransactionController::class, 'store'])->name('cash.store');

        Route::get('journals', [ReportController::class, 'journals'])->name('reports.journals');
        Route::get('ledger', [ReportController::class, 'ledger'])->name('reports.ledger');
        Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');

        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    // ===== Kasir & Owner: POS =====
    Route::middleware('role:owner,cashier')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
    });
});

require __DIR__.'/auth.php';
