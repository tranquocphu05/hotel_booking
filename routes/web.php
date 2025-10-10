<?php

use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Admin\LoaiPhongController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\VoucherController;

// Serve client dashboard at the site root as requested
Route::get('/', [ClientDashboardController::class, 'index'])->name('client.dashboard')->middleware([\App\Http\Middleware\AllowClient::class]);

Route::get('/dashboard', function () {
    // Redirect authenticated users to an appropriate dashboard.
    $user = Auth::user();
    if ($user && $user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('client.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/auth.php';


// Admin routes (only admin role)
Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
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
Route::prefix('client')->name('client.')->middleware([\App\Http\Middleware\AllowClient::class])->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
});


Route::resource('voucher', VoucherController::class)->names('voucher');

// impersonation: admin can impersonate a client user
Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])->name('impersonate');
Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop');


// Client routes (client and admin allowed). Keep legacy /client/dashboard for compatibility.


// Also make root ('/') available for client dashboard (already defined above). Old links to /client/dashboard still work.

// public (authenticated) route that allows current user to stop impersonation and return to admin (Cập nhật quản lý voucher)
Route::middleware('auth')->post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop.public');

