<?php

use App\Http\Controllers\ListController;
use App\Http\Controllers\ListMemberController;
use Illuminate\Support\Facades\Route;

// Rute resource untuk daftar tugas (list) - SRS-03
Route::resource('lists', ListController::class);

// Rute pengelolaan anggota kolaborator list - SRS-04
Route::post('lists/{list}/members', [ListMemberController::class, 'store'])->name('lists.members.store');
Route::delete('lists/{list}/members/{user}', [ListMemberController::class, 'destroy'])->name('lists.members.destroy');
