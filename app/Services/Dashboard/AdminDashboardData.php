<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\CampusCourse;
use App\Models\CampusCategory;
use App\Models\CampusSeason;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\CampusRegistration;
use Illuminate\Support\Facades\Log;

class AdminDashboardData
{
    public function build(): array
    {
        try {
            $season = CampusSeason::where('is_current', true)->first();

            return [
                'season' => $season,

                'stats' => [
                    'total_users'    => User::count(),
                    'admin_count'    => User::role('admin')->count(),
                    'teacher_count'  => User::role('teacher')->count(),
                    'student_count'  => User::role('student')->count(),

                    'total_courses'  => CampusCourse::count(),
                    'active_courses' => CampusCourse::where('is_active', true)->count(),
                    'inactive_courses' => CampusCourse::where('is_active', false)->count(),

                    'total_categories' => CampusCategory::count(),
                    'categories_with_courses' => CampusCategory::has('courses')->count(),

                    'total_registrations' => CampusRegistration::count(),
                    'active_registrations' => CampusRegistration::where('status', 'active')->count(),
                    'completed_registrations' => CampusRegistration::where('status', 'completed')->count(),

                    'total_seasons' => CampusSeason::count(),
                    'current_season' => $season?->name ?? 'No configurada',

                    'total_events' => Event::count(),

                    'total_feedback' => Feedback::count(),
                    'pending_feedback' => Feedback::where('status', 'pending')->count(),
                    'responded_feedback' => Feedback::where('status', 'responded')->count(),
                ],
            ];

        } catch (\Throwable $e) {
            Log::error('AdminDashboardData error: '.$e->getMessage());

            return [
                'season' => null,
                'stats' => [],
            ];
        }
    }
}
