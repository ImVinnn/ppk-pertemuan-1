<?php

use App\Http\Controllers\TodoListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('lists.index');
});

Route::resource('lists', TodoListController::class);
