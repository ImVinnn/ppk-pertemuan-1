<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListMember extends Model
{
    protected $table = 'list_user';

    protected $fillable = [
        'list_id',
        'user_id',
        'role',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
