<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CampusCourse;
use App\Models\CampusCategory;
use App\Models\CampusSeason;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\CampusRegistration;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Determinar qué dashboard mostrar
        $dashboardType = $this->getDashboardType($user);
        
        // Solo cargar estadísticas si es admin
        $stats = [];
        if ($dashboardType === 'admin') {
            $stats = $this->getAdminStats();
        }
        
       //  dd($stats, $user);
        // Pasar la información necesaria
        return view('dashboard', [
            'dashboard_type' => $dashboardType,
            'stats' => $stats,
            'user' => $user
        ]);
    }
    
    private function getDashboardType($user)
    {
        if ($user->hasAnyRole(['admin', 'super-admin']) || 
            $user->canany(['users.view', 'roles.index', 'permissions.index', 'settings.edit'])) {
            return 'admin';
        }
        
        if ($user->hasAnyRole(['gestor', 'editor', 'manager']) ||
            $user->canany(['events.view', 'campus.categories.view', 'campus.courses.view'])) {
            return 'manager';
        }
        
        if ($user->hasRole('teacher') || 
            $user->canany(['campus.my_courses.manage', 'campus.teacher-students.view'])) {
            return 'teacher';
        }
        
        if ($user->hasRole('student') || 
            $user->can('campus.my_courses.view')) {
            return 'student';
        }
        
        return 'basic';
    }
    
    private function getAdminStats()
    {
        try {
            return [
                'total_users' => User::count(),
                'admin_count' => User::role('admin')->count(),
                'teacher_count' => User::role('teacher')->count(),
                'student_count' => User::role('student')->count(),
                
                'total_courses' => CampusCourse::count(),
                'active_courses' => CampusCourse::where('is_active', true)->count(),
                'inactive_courses' => CampusCourse::where('is_active', false)->count(),
                
                'total_categories' => CampusCategory::count(),
                'categories_with_courses' => CampusCategory::has('courses')->count(),
                
                'total_registrations' => CampusRegistration::count(),
                'active_registrations' => CampusRegistration::where('status', 'active')->count(),
                'completed_registrations' => CampusRegistration::where('status', 'completed')->count(),
                
                'total_seasons' => CampusSeason::count(),
                'current_season' => CampusSeason::where('is_current', true)->first()->name ?? 'No configurada',
                
                'total_events' => Event::count(),
                'upcoming_events' => 0,
                'past_events' => 0,
                
                'total_feedback' => Feedback::count(),
                'pending_feedback' => Feedback::where('status', 'pending')->count(),
                'responded_feedback' => Feedback::where('status', 'responded')->count(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error en getAdminStats: ' . $e->getMessage());
            return ['error' => true];
        }
    }
}