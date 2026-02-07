<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;


class CampusTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_code',
        'first_name',
        'last_name',
        'dni',
        'email',
        'phone',
        'specialization',
        'title',
        'areas',
        'status',
        'hiring_date',
        'metadata'
    ];

    protected $casts = [
        'hiring_date' => 'date',
        'areas' => 'array',
        'metadata' => 'array'
    ];

    /**
     * Get the user that owns the teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the courses taught by the teacher.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            CampusCourse::class,
            'campus_course_teacher',  // nom de la taula pivot
            'teacher_id',             // foreign key en la taula pivot per a CampusTeacher
            'course_id',              // foreign key en la taula pivot per a CampusCourse
            'id',                     // local key en la taula CampusTeacher
            'id'                      // local key en la taula CampusCourse
        )->withPivot('role', 'hours_assigned', 'assigned_at', 'finished_at', 'metadata')
        ->withTimestamps();
        
    }


    /**
     * Get current courses (active and not finished).
     */
    public function currentCourses()
    {
        return $this->courses()
                    ->where('is_active', true)
                    ->where('end_date', '>=', now())
                    ->wherePivot('finished_at', null);
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        $fullName = "{$this->first_name} {$this->last_name}";
        
        if (!empty($this->title)) {
            $fullName = "{$this->title} {$fullName}";
        }
        
        return $fullName;
    }

    /**
     * Get formatted specialization.
     */
    public function getFormattedSpecializationAttribute(): ?string
    {
        if (empty($this->areas)) {
            return $this->specialization;
        }
        
        $areas = implode(', ', $this->areas);
        return $this->specialization 
            ? "{$this->specialization} ({$areas})"
            : $areas;
    }

    /**
     * Scope for active teachers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if teacher is currently teaching any course.
     */
    public function isCurrentlyTeaching(): bool
    {
        return $this->currentCourses()->exists();
    }

    /**
     * Get total hours assigned across all courses.
     */
    public function getTotalAssignedHoursAttribute(): float
    {
        return $this->courses()
                    ->wherePivot('finished_at', null)
                    ->sum('campus_course_teacher.hours_assigned');
    }

        /**
     * Get the latest active course assignment.
     */
    public function currentCourse()
    {
        return $this->courses()
            ->wherePivotNull('finished_at')
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->orderBy('campus_course_teacher.assigned_at', 'desc')
            ->first();
    }

    /**
     * Get detailed course assignments with season and category info.
     */
    public function detailedCourseAssignments()
    {
        return $this->courses()
            ->wherePivotNull('finished_at')
            ->with(['season', 'category'])
            ->select(
                'campus_courses.*',
                'campus_course_teacher.role',
                'campus_course_teacher.hours_assigned',
                'campus_course_teacher.assigned_at',
                'campus_course_teacher.finished_at'
            )
            ->orderBy('campus_course_teacher.assigned_at', 'desc')
            ->get();
    }

    /**
     * Get courses grouped by role.
     */
    public function coursesByRole()
    {
        return $this->courses()
            ->wherePivotNull('finished_at')
            ->get()
            ->groupBy('pivot.role');
    }

    /**
     * Check if teacher has active course assignments.
     */
    public function hasActiveAssignments(): bool
    {
        return $this->courses()
            ->wherePivotNull('finished_at')
            ->exists();
    }

}