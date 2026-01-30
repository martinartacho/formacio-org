<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentHistory extends Model
{
    protected $fillable = [
        'teacher_id',
        'season',
        'document_path',
        'accepted_at',
        'checksum',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
