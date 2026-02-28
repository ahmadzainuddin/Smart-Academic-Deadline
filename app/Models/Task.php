<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'category',
        'due_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_at' => 'datetime:Y-m-d\TH:i:s',
        'created_at' => 'datetime:Y-m-d\TH:i:s',
        'updated_at' => 'datetime:Y-m-d\TH:i:s',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
