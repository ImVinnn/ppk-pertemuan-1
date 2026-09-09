<?php

namespace App\Policies;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TodoListPolicy
{
    /**
     * Menentukan apakah user dapat melihat daftar list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah user dapat melihat detail list (Owner atau Anggota tim).
     */
    public function view(User $user, TodoList $todoList): bool
    {
        return $todoList->hasAccess($user);
    }

    /**
     * Menentukan apakah user dapat membuat list baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah user dapat mengedit list (Hanya Owner).
     */
    public function update(User $user, TodoList $todoList): bool
    {
        return $todoList->isOwner($user);
    }

    /**
     * Menentukan apakah user dapat menghapus list (Hanya Owner).
     */
    public function delete(User $user, TodoList $todoList): bool
    {
        return $todoList->isOwner($user);
    }

    /**
     * Menentukan apakah user dapat mengelola anggota (tambah/keluarkan anggota - Hanya Owner).
     */
    public function manageMembers(User $user, TodoList $todoList): bool
    {
        return $todoList->isOwner($user);
    }
}
