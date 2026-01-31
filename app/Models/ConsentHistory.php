<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentHistory extends Model
{
     use HasFactory;
     
     protected $fillable = [
        'teacher_id',
        'season',
        'document_path',
        'accepted_at',
        'checksum',
        'delegated_by_user_id',
        'delegated_reason',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];



    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function delegatedBy()
    {
        return $this->belongsTo(User::class, 'delegated_by_user_id');
    }

}
