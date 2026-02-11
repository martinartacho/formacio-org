<?php

namespace App\Services\Dashboard;

use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\CampusRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TeacherDashboardData
{
    public function build($user): array
    {
        try {
            Log::info('TeacherDashboardData - User ID: ' . $user->id);

            // Verificar si el usuario tiene rol de profesor
            if (!$user->hasRole('teacher')) {
                Log::warning('TeacherDashboardData - User does not have teacher role');
                return [
                    'teacher' => null,
                    'teacherCourses' => collect(),
                    'stats' => [],
                    'season' => null,
                    'seasons' => collect(),
                    'allCourses' => collect(),
                    'debug' => 'User does not have teacher role'
                ];
            }

            // Buscar el perfil de profesor
            $teacher = CampusTeacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                Log::warning('TeacherDashboardData - No teacher profile found for user ID: ' . $user->id);
                return [
                    'teacher' => null,
                    'teacherCourses' => collect(),
                    'stats' => [],
                    'season' => null,
                    'seasons' => collect(),
                    'allCourses' => collect(),
                    'debug' => 'No teacher profile found'
                ];
            }

            Log::info('TeacherDashboardData - Teacher found: ' . $teacher->teacher_code);

            // Obtener temporada actual
            $currentSeason = CampusSeason::where('is_current', true)->first();
            $seasons = CampusSeason::active()->orderBy('season_start', 'desc')->get();

            Log::info('TeacherDashboardData - Current season: ' . ($currentSeason ? $currentSeason->name : 'none'));

            // Obtener cursos del profesor con información completa
            $coursesQuery = $teacher->courses()
                ->with([
                    'season', 
                    'category',
                    'teachers' => function ($query) {
                        // Cargar todos los profesores del curso
                        $query->with('user')->orderBy('campus_course_teacher.role');
                    },
                    'registrations' => function ($query) {
                        // Cargar registros confirmados y completados
                        $query->whereIn('status', ['confirmed', 'completed'])
                              ->with('student.user');
                    }
                ])
                ->withCount([
                    'registrations as confirmed_students_count' => function ($q) {
                        $q->whereIn('status', ['confirmed', 'completed']);
                    }
                ]);

            // Si hay temporada actual, filtrar por ella
            if ($currentSeason) {
                $coursesQuery->where('season_id', $currentSeason->id);
                Log::info('TeacherDashboardData - Filtering by season: ' . $currentSeason->id);
            }

            $teacherCourses = $coursesQuery->get();
            
            // Procesar cada curso para añadir información adicional
            $processedCourses = $teacherCourses->map(function ($course) use ($teacher) {
                // Determinar status del curso
                $now = now();
                $courseStatus = 'upcoming';
                
                if ($course->start_date && $course->end_date) {
                    if ($now->between($course->start_date, $course->end_date)) {
                        $courseStatus = 'active';
                    } elseif ($now->gt($course->end_date)) {
                        $courseStatus = 'completed';
                    }
                }
                
                // Obtener otros profesores (excluyendo el profesor actual)
                $otherTeachers = $course->teachers->filter(function ($courseTeacher) use ($teacher) {
                    return $courseTeacher->id !== $teacher->id;
                });
                
                // Procesar horario
                $scheduleInfo = $this->processSchedule($course);
                
                // Estudiantes matriculados
                $students = $course->registrations->map(function ($registration) {
                    return [
                        'id' => $registration->student->id,
                        'name' => $registration->student->user->name ?? 
                                 ($registration->student->first_name . ' ' . $registration->student->last_name),
                        'status' => $registration->status,
                        'registration_date' => $registration->registration_date
                    ];
                });
                
                return (object) [
                    'id' => $course->id,
                    'code' => $course->code,
                    'title' => $course->title,
                    'description' => $course->description,
                    'start_date' => $course->start_date,
                    'end_date' => $course->end_date,
                    'hours' => $course->hours,
                    'credits' => $course->credits,
                    'max_students' => $course->max_students,
                    'level' => $course->level,
                    'is_public' => $course->is_public,
                    'category' => $course->category,
                    'season' => $course->season,
                    'pivot' => $course->pivot,
                    'confirmed_students_count' => $course->confirmed_students_count,
                    'teachers' => $course->teachers,
                    'other_teachers' => $otherTeachers,
                    'students' => $students,
                    'schedule' => $course->schedule,
                    'schedule_info' => $scheduleInfo,
                    'status' => $courseStatus,
                    'available_spots' => $course->available_spots ?? 0,
                    'formatted_schedule' => $course->formatted_schedule
                ];
            });

            // Calcular estadísticas
            $stats = $this->calculateStatistics($processedCourses, $teacher);

            Log::info('TeacherDashboardData - Stats calculated', $stats);

            return [
                'teacher' => $teacher,
                'season' => $currentSeason,
                'seasons' => $seasons,
                'teacherCourses' => $processedCourses,
                'allCourses' => $teacherCourses,
                'stats' => $stats,
                'debug' => [
                    'user_id' => $user->id,
                    'has_teacher_role' => $user->hasRole('teacher'),
                    'teacher_found' => $teacher ? true : false,
                    'courses_count' => $teacherCourses->count(),
                ]
            ];

        } catch (\Throwable $e) {
            Log::error('TeacherDashboardData error: ' . $e->getMessage());
            Log::error('TeacherDashboardData trace: ' . $e->getTraceAsString());
            
            return [
                'teacher' => null,
                'teacherCourses' => collect(),
                'stats' => [],
                'season' => null,
                'seasons' => collect(),
                'allCourses' => collect(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Procesar información del horario
     */
    private function processSchedule($course): array
    {
        $info = [
            'has_schedule' => false,
            'today_classes' => [],
            'next_class' => null,
            'formatted' => null
        ];
        
        if (!empty($course->schedule) && is_array($course->schedule)) {
            $info['has_schedule'] = true;
            $info['formatted'] = $course->formatted_schedule;
            
            // Mapear días en catalán a números (1=Lunes, 7=Domingo)
            $dayMap = [
                'Dilluns' => 1,
                'Dimarts' => 2,
                'Dimecres' => 3,
                'Dijous' => 4,
                'Divendres' => 5,
                'Dissabte' => 6,
                'Diumenge' => 7
            ];
            
            $today = Carbon::now()->dayOfWeekIso; // 1=Lunes, 7=Domingo
            $currentTime = Carbon::now()->format('H:i');
            
            foreach ($course->schedule as $schedule) {
                $dayName = $schedule['day'];
                $dayNumber = $dayMap[$dayName] ?? 0;
                
                if ($dayNumber > 0) {
                    // Clases de hoy
                    if ($dayNumber == $today) {
                        $info['today_classes'][] = [
                            'day' => $dayName,
                            'start' => $schedule['start'],
                            'end' => $schedule['end'],
                            'is_current' => ($currentTime >= $schedule['start'] && $currentTime <= $schedule['end'])
                        ];
                    }
                    
                    // Próxima clase
                    if (!$info['next_class'] && $dayNumber >= $today) {
                        $info['next_class'] = [
                            'day' => $dayName,
                            'start' => $schedule['start'],
                            'end' => $schedule['end'],
                            'days_until' => $dayNumber - $today
                        ];
                    }
                }
            }
        }
        
        return $info;
    }
    
    /**
     * Calcular estadísticas
     */
    private function calculateStatistics($courses, $teacher): array
    {
        $totalStudents = 0;
        $activeCoursesCount = 0;
        $todayClasses = 0;
        $upcomingClasses = 0;
        
        foreach ($courses as $course) {
            $totalStudents += $course->confirmed_students_count;
            
            if ($course->status === 'active') {
                $activeCoursesCount++;
                
                // Contar clases de hoy
                if ($course->schedule_info['has_schedule']) {
                    $todayClasses += count($course->schedule_info['today_classes']);
                }
            }
            
            // Contar próximas clases
            if ($course->schedule_info['next_class']) {
                $upcomingClasses++;
            }
        }
        
        // Matrículas pendientes (registros con status 'pending')
        $upcomingRegistrations = CampusRegistration::whereIn('course_id', $courses->pluck('id'))
            ->where('status', 'pending')
            ->count();
        
        return [
            'total_courses' => $courses->count(),
            'active_courses' => $activeCoursesCount,
            'total_students' => $totalStudents,
            'today_classes' => $todayClasses,
            'upcoming_classes' => $upcomingClasses,
            'completed_courses' => $courses->where('status', 'completed')->count(),
            'upcoming_registrations' => $upcomingRegistrations,
        ];
    }

    public static function from(User $user): array
    {
        $teacher = $user->teacher; // relación User -> CampusTeacher

        $courses = $teacher
            ->courses()
            ->withCount('students')
            ->with('campus')
            ->get()
            ->map(fn ($course) => [
                'id'        => $course->id,
                'name'      => $course->name,
                'campus'    => $course->campus?->name,
                'students'  => $course->students_count,
                'role'      => $course->pivot->role,
                'hours'     => $course->pivot->hours_assigned,
                'is_main'   => $course->pivot->role === 'main',
            ]);

        return [
            'teacher' => $teacher,
            'courses' => $courses,
        ];
    }
}