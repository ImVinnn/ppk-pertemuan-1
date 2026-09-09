<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TodoList extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

    /**
     * Pemilik (Owner) dari list.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Anggota tim yang diajak berkolaborasi dalam list.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_user', 'list_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Cek apakah user adalah owner dari list.
     */
    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Cek apakah user adalah anggota tim list.
     */
    public function isMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Cek apakah user memiliki akses (sebagai owner atau member).
     */
    public function hasAccess(User $user): bool
    {
        return $this->isOwner($user) || $this->isMember($user);
    }
}
