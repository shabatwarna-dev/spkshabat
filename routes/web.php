<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionProcessController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TeamController;

// ── Auth ──────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Protected ─────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Push notification subscribe/unsubscribe
    Route::post('/notifications/subscribe',   [NotificationController::class, 'subscribe'])->name('notifications.subscribe');
    Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe'])->name('notifications.unsubscribe');

    // Daftar & detail SPK — semua role bisa lihat
    Route::get('/spk', [ProductionOrderController::class, 'index'])->name('orders.index');
    Route::get('/spk/riwayat', [ProductionOrderController::class, 'history'])->name('orders.history');
    Route::get('/spk/{order}', [ProductionOrderController::class, 'show'])->name('orders.show');
    Route::get('/spk/{order}/pdf', [ProductionOrderController::class, 'exportPdf'])->name('orders.pdf');

    // PPIC only — buat & kelola SPK
    Route::middleware(['role:ppic'])->group(function () {
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

    // Koor & Operator — input hasil produksi
    Route::middleware(['role:koor,operator'])->group(function () {
        Route::put('/proses/{process}/produksi', [ProductionProcessController::class, 'updateProduksi'])->name('processes.updateProduksi');
    });

    // Laporan — ppic & koor saja
    Route::middleware(['role:ppic,koor'])->group(function () {
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    });

    // ── Master Admin only ──────────────────────────────────────
    Route::middleware(['role:master_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/buat', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/buat', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    });
});

// Storage fallback untuk local development
if (app()->environment('local')) {
    Route::get('/storage/{path}', function (string $path) {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) abort(404);
        return response()->file($fullPath, ['Content-Type' => mime_content_type($fullPath)]);
    })->where('path', '.*');
}
