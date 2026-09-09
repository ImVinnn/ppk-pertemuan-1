<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'title',
        'description',
        'priority',
        'due_date',
        'is_done',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_done'  => 'boolean',
    ];

    public function list()
    {
        $relatedClass = class_exists(TodoList::class) ? TodoList::class : TaskList::class;
        return $this->belongsTo($relatedClass, 'list_id');
    }
}
