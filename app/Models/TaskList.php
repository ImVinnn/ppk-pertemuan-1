<?php

namespace App\Models;

/**
 * Model TaskList merepresentasikan entitas daftar tugas (alias spesifikasi SRS untuk TodoList).
 */
class TaskList extends TodoList
{
    protected $table = 'todo_lists';
}
