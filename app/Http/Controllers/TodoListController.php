<?php

namespace App\Http\Controllers;

/**
 * TodoListController mewarisi ListController untuk kompatibilitas rute yang sudah terdaftar.
 */
class TodoListController extends ListController
{
    // Mewarisi method store, destroy atomik, show, index, dll dari ListController
}
