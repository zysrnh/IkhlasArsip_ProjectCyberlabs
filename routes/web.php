<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenReportController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
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

    // Modul Lupa Password & Reset Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Kelola Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    // Modul Input Masakan Dapur Harian (Sheet 1)
    Route::get('/kitchen-reports/{kitchenReport}/export-pdf', [KitchenReportController::class, 'exportPdf'])->name('kitchen-reports.export-pdf');
    Route::resource('kitchen-reports', KitchenReportController::class);

    // Modul Data Transaksi & Laporan
    Route::get('/transactions/download-template', [TransactionController::class, 'downloadTemplate'])->name('transactions.download-template');
    Route::get('/transactions/export-pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export-pdf');
    Route::get('/transactions/export-excel', [TransactionController::class, 'exportExcel'])->name('transactions.export-excel');
    Route::post('/transactions/import-excel', [TransactionController::class, 'importExcel'])->name('transactions.import-excel');
    Route::post('/transactions/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulk-delete');
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit']);

    // Modul Master Menu Masakan & Harga Cabang
    Route::post('/menus/{menu}/prices', [MenuController::class, 'updatePrices'])->name('menus.prices.update');
    Route::resource('menus', MenuController::class)->except(['create', 'show', 'edit']);

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

        // Modul Backup Database
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{filename}/send-email', [BackupController::class, 'sendEmail'])->name('backups.send-email');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});
