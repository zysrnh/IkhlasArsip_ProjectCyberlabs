<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Data Transaksi & Laporan
    Route::get('/transactions/download-template', [TransactionController::class, 'downloadTemplate'])->name('transactions.download-template');
    Route::get('/transactions/export-pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export-pdf');
    Route::get('/transactions/export-excel', [TransactionController::class, 'exportExcel'])->name('transactions.export-excel');
    Route::post('/transactions/import-excel', [TransactionController::class, 'importExcel'])->name('transactions.import-excel');
    Route::post('/transactions/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulk-delete');
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit']);

    // Modul Manajemen User (Super Admin & Kepala Cabang)
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    // Khusus Super Admin
    Route::middleware('superadmin')->group(function () {
        // Modul Manajemen Cabang
        Route::resource('branches', BranchController::class)->except(['create', 'show', 'edit']);

        // Modul Sampah / Recycle Bin
        Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
        Route::post('/trash/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
        Route::delete('/trash/{id}/force', [TrashController::class, 'forceDelete'])->name('trash.force-delete');
        Route::post('/trash/bulk-restore', [TrashController::class, 'bulkRestore'])->name('trash.bulk-restore');
        Route::post('/trash/bulk-force-delete', [TrashController::class, 'bulkForceDelete'])->name('trash.bulk-force-delete');
        Route::delete('/trash/empty', [TrashController::class, 'emptyTrash'])->name('trash.empty');
    });
});
