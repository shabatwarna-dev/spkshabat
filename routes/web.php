<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionProcessController;
use App\Http\Controllers\ReportController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (both roles)
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/spk', [ProductionOrderController::class, 'index'])->name('orders.index');
    Route::get('/spk/riwayat', [ProductionOrderController::class, 'history'])->name('orders.history');

    // Marketing-only routes — HARUS sebelum /spk/{order}
    Route::middleware(['role:marketing'])->group(function () {
        Route::get('/spk/buat', [ProductionOrderController::class, 'create'])->name('orders.create');
        Route::post('/spk', [ProductionOrderController::class, 'store'])->name('orders.store');
        Route::get('/spk/{order}/edit', [ProductionOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/spk/{order}', [ProductionOrderController::class, 'update'])->name('orders.update');
        Route::delete('/spk/{order}', [ProductionOrderController::class, 'destroy'])->name('orders.destroy');

        Route::post('/spk/{order}/proses', [ProductionProcessController::class, 'store'])->name('processes.store');
        Route::put('/proses/{process}/marketing', [ProductionProcessController::class, 'updateMarketing'])->name('processes.updateMarketing');
        Route::put('/proses/{process}/status', [ProductionProcessController::class, 'updateStatus'])->name('processes.updateStatus');
        Route::post('/spk/{order}/proses/reorder', [ProductionProcessController::class, 'reorder'])->name('processes.reorder');
        Route::delete('/proses/{process}', [ProductionProcessController::class, 'destroy'])->name('processes.destroy');
    });

    // Route dengan {order} wildcard — setelah route statis
    Route::get('/spk/{order}', [ProductionOrderController::class, 'show'])->name('orders.show');
    Route::get('/spk/{order}/pdf', [ProductionOrderController::class, 'exportPdf'])->name('orders.pdf');

    // Produksi update results
    Route::middleware(['role:produksi,marketing'])->group(function () {
        Route::put('/proses/{process}/produksi', [ProductionProcessController::class, 'updateProduksi'])->name('processes.updateProduksi');
    });

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
});
