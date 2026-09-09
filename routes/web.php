<?php

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