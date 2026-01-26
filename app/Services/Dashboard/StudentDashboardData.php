<?php

namespace App\Services\Dashboard;

use App\Models\CampusSeason as Season;
use App\Models\CampusRegistration as Registration;

class StudentDashboardData
{
    public function build($user): array
    {
        $student = $user->student;
       //  abort_if(!$student, 403);
dd($student);
        $season = Season::active()->first();

        $registrations = Registration::query()
            ->where('student_id', $student->id)
            ->when($season, fn ($q) => $q->where('season_id', $season->id))
            ->with(['course.category'])
            ->get();

        $courses = $registrations
            ->pluck('course')
            ->filter();

        return [
            'season' => $season,
            'courses' => $courses,
            'stats' => [
                'total_courses'  => $courses->count(),
                'active_courses' => $courses->where('is_active', true)->count(),
                'total_students' => null, // no aplica
            ],
        ];
    }
}
