<?php

use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\InvoiceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// =======================
// Client dashboard (default home)
// =======================
Route::get('/', [ClientDashboardController::class, 'index'])
    ->name('client.dashboard')
    ->middleware([\App\Http\Middleware\AllowClient::class]);

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user && $user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('client.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// =======================
// Profile routes
// =======================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // CRUD routes
    Route::resource('loai_phong', LoaiPhongController::class)->names('loai_phong');
    Route::resource('voucher', VoucherController::class)->names('voucher');
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');

    // Impersonation (admin only)
    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])
        ->name('impersonate');
    Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
        ->name('impersonate.stop');

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])->name('impersonate');
    Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop');

    Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::get('/{id}', [CommentController::class, 'show'])->name('show');
            Route::put('/{id}/reply', [CommentController::class, 'reply'])->name('reply');
            Route::put('/{id}/toggle', [CommentController::class, 'toggleStatus'])->name('toggle');
        });

});

// =======================
// Client routes
// =======================
Route::prefix('client')->name('client.')->middleware([\App\Http\Middleware\AllowClient::class])->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
    ->name('impersonate.stop.public');
=======


Route::resource('voucher', VoucherController::class)->names('voucher');

// impersonation: admin can impersonate a client user
Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])->name('impersonate');
Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop');


// Client routes (client and admin allowed). Keep legacy /client/dashboard for compatibility.


// Also make root ('/') available for client dashboard (already defined above). Old links to /client/dashboard still work.

// public (authenticated) route that allows current user to stop impersonation and return to admin (Cập nhật quản lý voucher)
Route::middleware('auth')->post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop.public');
