<?php

use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\VoucherController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    Route::resource('voucher', VoucherController::class)->names('voucher');
    Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::get('/{id}', [CommentController::class, 'show'])->name('show');
            Route::put('/{id}/reply', [CommentController::class, 'reply'])->name('reviews.reply');
            Route::delete('/{id}/reply', [CommentController::class, 'deleteReply'])->name('reviews.reply.delete');
            Route::put('/{id}/toggle', [CommentController::class, 'statusToggle'])->name('toggle');
        });
    // impersonation
    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])
        ->name('impersonate');
    Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
        ->name('impersonate.stop');
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
