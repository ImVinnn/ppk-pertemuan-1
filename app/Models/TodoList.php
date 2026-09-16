<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoList extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'name',
        'owner_id',
    ];

    // Accessor & Mutator untuk 'name' agar kompatibel dengan spesifikasi TaskList
    public function getNameAttribute(): ?string
    {
        return $this->attributes['title'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['title'] = $value;
    }

    // Accessor & Mutator untuk 'owner_id' agar kompatibel dengan spesifikasi TaskList
    public function getOwnerIdAttribute(): ?int
    {
        return isset($this->attributes['user_id']) ? (int) $this->attributes['user_id'] : null;
    }

    public function setOwnerIdAttribute(?int $value): void
    {
        $this->attributes['user_id'] = $value;
    }

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
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Semua tugas (task) di dalam list ini.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'list_id');
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
