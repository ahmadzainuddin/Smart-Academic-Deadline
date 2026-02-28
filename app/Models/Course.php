<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'code',
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
