<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;


class CampusTeacherPayment extends Model
{
    protected $fillable = [
        'teacher_id',
        'course_id',
        'season_id',
        'payment_option',
        'first_name',
        'last_name',
        'last_name2',
        'dni',
        'postal_code',
        'iban',
        'bank_holder',
    ];

    public function teacher()
    {
        return $this->belongsTo(CampusTeacher::class);
    }

    public function course()
    {
        return $this->belongsTo(CampusCourse::class);
    }

    public function season()
    {
        return $this->belongsTo(CampusSeason::class);
    }
}
