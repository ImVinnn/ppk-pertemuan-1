<?php

use App\Http\Controllers\ListMemberController;
use App\Http\Controllers\TodoListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('lists.index');
});

Route::resource('lists', TodoListController::class);

// Rute Pengelolaan Anggota Kolaborator List
Route::post('lists/{list}/members', [ListMemberController::class, 'store'])->name('lists.members.store');
Route::delete('lists/{list}/members/{user}', [ListMemberController::class, 'destroy'])->name('lists.members.destroy');
