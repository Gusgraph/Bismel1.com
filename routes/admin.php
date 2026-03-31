<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: routes/admin.php
// ======================================================

use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\AccountDetailController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseManagementController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/accounts', [AccountManagementController::class, 'index'])->name('accounts.index');
Route::get('/system', [SystemController::class, 'index'])->name('system.index');
Route::get('/system/edit', [SystemController::class, 'edit'])->name('system.edit');
Route::put('/system', [SystemController::class, 'update'])->name('system.update');
Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/licenses', [LicenseManagementController::class, 'index'])->name('licenses.index');
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::get('/account-detail/{account}', [AccountDetailController::class, 'index'])->name('account-detail.index');
Route::post('/account-detail/{account}/operator-action', [AccountDetailController::class, 'operatorAction'])->name('account-detail.operator-action');
