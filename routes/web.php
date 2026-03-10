<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\IRacingOAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ManagerController;
use App\Models\RaceResult;

Route::get('/', function () {
    return view('enosis-landing');
});

Route::get('/calendar', function () {
    $upcoming = RaceResult::query()
        ->whereNotNull('race_date')
        ->where('race_date', '>=', now())
        ->orderBy('race_date')
        ->limit(20)
        ->get();

    return view('calendar', [
        'upcoming' => $upcoming,
    ]);
})->name('calendar');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/auth/iracing/redirect', [IRacingOAuthController::class, 'redirect'])->name('iracing.oauth.redirect');
    Route::get('/auth/iracing/callback', [IRacingOAuthController::class, 'callback'])->name('iracing.oauth.callback');
    Route::post('/auth/iracing/unlink', [IRacingOAuthController::class, 'unlink'])->name('iracing.oauth.unlink');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->role === 'driver') {
        return redirect()->route('driver.profile');
    }

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('manager.dashboard');
})->name('dashboard')->middleware('auth');

Route::middleware(['auth', 'iracing.refresh', 'role:driver'])->group(function () {
    Route::get('/driver/profile', [DriverController::class, 'profile'])->name('driver.profile');
});

Route::middleware(['auth', 'role:manager,admin'])->group(function () {
    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/manager/leaderboard', [ManagerController::class, 'leaderboard'])->name('manager.leaderboard');
    Route::get('/manager/calendar', [ManagerController::class, 'calendar'])->name('manager.calendar');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/managers', [AdminController::class, 'createManager'])->name('admin.managers.create');
});
