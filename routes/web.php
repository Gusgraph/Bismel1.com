<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: routes/web.php
// ======================================================

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TestSpeedExecuteCheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/plans', [HomeController::class, 'plans'])->name('plans');
Route::get('/plans/test/speed-execute', [TestSpeedExecuteCheckoutController::class, 'start'])->name('plans.test.speed-execute.start');
Route::get('/plans/test/speed-execute/success', [TestSpeedExecuteCheckoutController::class, 'success'])->name('plans.test.speed-execute.success');
Route::get('/plans/test/speed-execute/cancel', [TestSpeedExecuteCheckoutController::class, 'cancel'])->name('plans.test.speed-execute.cancel');
Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->name('login.store');
Route::get('/signup', [SignupController::class, 'create'])->name('signup');
Route::post('/signup', [SignupController::class, 'store'])->name('signup.store');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::middleware('customer.area')
    ->prefix('customer')
    ->name('customer.')
    ->group(base_path('routes/customer.php'));

Route::middleware('admin.area')
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));
