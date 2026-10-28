<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UserRoleController;
use App\Http\Middleware\TrackUserActivity;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : view('welcome'));

Route::middleware(['auth', TrackUserActivity::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('can:lihat buku')
        ->name('dashboard');

    Route::get('/buku', [BukuController::class, 'index'])
        ->middleware('can:lihat buku')
        ->name('buku.index');
    Route::get('/buku/create', [BukuController::class, 'create'])
        ->middleware('can:tambah buku')
        ->name('buku.create');
    Route::post('/buku', [BukuController::class, 'store'])
        ->middleware('can:tambah buku')
        ->name('buku.store');
    Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])
        ->middleware('can:edit buku')
        ->name('buku.edit');
    Route::put('/buku/{buku}', [BukuController::class, 'update'])
        ->middleware('can:edit buku')
        ->name('buku.update');
    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])
        ->middleware('can:hapus buku')
        ->name('buku.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users/roles', [UserRoleController::class, 'index'])
        ->middleware('can:kelola role pengguna')
        ->name('users.roles.index');
    Route::patch('/users/{user}/role', [UserRoleController::class, 'update'])
        ->middleware('can:kelola role pengguna')
        ->name('users.roles.update');
    Route::patch('/users/{user}/status', [UserRoleController::class, 'toggleAccount'])
        ->middleware('can:kelola role pengguna')
        ->name('users.status.toggle');
    Route::get('/users/management', [UserManagementController::class, 'index'])
        ->middleware('can:kelola role pengguna')
        ->name('users.management.index');
    Route::post('/users/management', [UserManagementController::class, 'store'])
        ->middleware('can:kelola role pengguna')
        ->name('users.management.store');
    Route::post('/users/password-reset-requests/{passwordResetRequest}/approve', [UserManagementController::class, 'approve'])
        ->middleware('can:kelola role pengguna')
        ->name('users.password-reset-requests.approve');
    Route::post('/users/password-reset-requests/{passwordResetRequest}/reject', [UserManagementController::class, 'reject'])
        ->middleware('can:kelola role pengguna')
        ->name('users.password-reset-requests.reject');
    Route::delete('/users/password-reset-requests/{passwordResetRequest}', [UserManagementController::class, 'destroy'])
        ->middleware('can:kelola role pengguna')
        ->name('users.password-reset-requests.destroy');
});

require __DIR__.'/auth.php';
