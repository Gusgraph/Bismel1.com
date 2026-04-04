<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: routes/customer.php
// ======================================================

use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AutomationController;
use App\Http\Controllers\Customer\BillingController;
use App\Http\Controllers\Customer\BillingCheckoutController;
use App\Http\Controllers\Customer\BrokerController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\LicenseController;
use App\Http\Controllers\Customer\OnboardingController;
use App\Http\Controllers\Customer\ReportsController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\Customer\StrategyController;
use App\Http\Controllers\Customer\TradingPagesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/account', [AccountController::class, 'index'])->name('account.index');
Route::get('/broker', [BrokerController::class, 'index'])->name('broker.index');
Route::get('/broker/create', [BrokerController::class, 'create'])->name('broker.create');
Route::post('/broker', [BrokerController::class, 'store'])->name('broker.store');
Route::get('/strategy', [StrategyController::class, 'index'])->name('strategy.index');
Route::get('/strategy/prime-stocks', [StrategyController::class, 'primeStocks'])->name('strategy.prime-stocks');
Route::put('/strategy', [StrategyController::class, 'update'])->name('strategy.update');
Route::get('/automation', [AutomationController::class, 'index'])->name('automation.index');
Route::put('/automation', [AutomationController::class, 'update'])->name('automation.update');
Route::get('/positions', [TradingPagesController::class, 'positions'])->name('positions.index');
Route::get('/orders', [TradingPagesController::class, 'orders'])->name('orders.index');
Route::get('/activity', [TradingPagesController::class, 'activity'])->name('activity.index');
Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
Route::post('/billing/checkout', [BillingCheckoutController::class, 'store'])->name('billing.checkout.store');
Route::get('/billing/checkout/success', [BillingCheckoutController::class, 'success'])->name('billing.checkout.success');
Route::get('/billing/checkout/cancel', [BillingCheckoutController::class, 'cancel'])->name('billing.checkout.cancel');
Route::get('/license', [LicenseController::class, 'index'])->name('license.index');
Route::get('/license/create', [LicenseController::class, 'create'])->name('license.create');
Route::post('/license', [LicenseController::class, 'store'])->name('license.store');
Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::get('/settings/edit', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
