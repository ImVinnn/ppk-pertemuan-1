<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListMemberController;
use App\Http\Controllers\TodoListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('lists.index');
});

// Hanya untuk pengguna yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');
});

// Hanya untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Hanya untuk admin
    Route::middleware('admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');
    });

    Route::resource('lists', TodoListController::class);

    // Pengelolaan anggota kolaborator list
    Route::post(
        'lists/{list}/members',
        [ListMemberController::class, 'store']
    )->name('lists.members.store');

    Route::delete(
        'lists/{list}/members/{user}',
        [ListMemberController::class, 'destroy']
    )->name('lists.members.destroy');
});