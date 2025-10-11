<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\DatPhongController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;

// Serve client dashboard at the site root
Route::get('/', [ClientDashboardController::class, 'index'])
    ->name('client.dashboard')
    ->middleware([\App\Http\Middleware\AllowClient::class]);

Route::get('/dashboard', function () {
    // Redirect authenticated users to their role dashboard
    $user = Auth::user();
    if ($user && $user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('client.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// =======================
// Admin routes
// =======================
Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
    Route::resource('loai_phong', LoaiPhongController::class)->names('loai_phong');
    Route::resource('phong', PhongController::class)->names('phong');
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    Route::resource('voucher', VoucherController::class)->names('voucher');

    // impersonation
    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])
        ->name('impersonate');
    Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
        ->name('impersonate.stop');

    // Nhóm route cho đặt phòng
    Route::prefix('dat_phong')->name('dat_phong.')->group(function () {
        Route::get('/', [DatPhongController::class, 'index'])->name('index');
        Route::get('/create', [DatPhongController::class, 'create'])->name('create');
        Route::post('/', [DatPhongController::class, 'store'])->name('store');
        Route::get('/{id}', [DatPhongController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DatPhongController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DatPhongController::class, 'update'])->name('update');
        Route::delete('/{id}', [DatPhongController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/cancel', [DatPhongController::class, 'showCancelForm'])->name('cancel');
        Route::post('/{id}/cancel', [DatPhongController::class, 'submitCancel'])->name('cancel.submit');
    });
});

// =======================
// Client routes
// =======================
Route::prefix('client')->name('client.')->middleware([\App\Http\Middleware\AllowClient::class])->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
});

// Public impersonation stop (in case admin is impersonating)
Route::middleware('auth')->post(
    '/impersonate/stop',
    [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop']
)->name('impersonate.stop.public');
