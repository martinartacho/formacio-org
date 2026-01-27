<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * Mostrar los cursos del profesor
     */
    public function courses()
    {
        $teacher = Auth::user()->teacher;
        
        if (!$teacher) {
            abort(403, 'No tienes un perfil de profesor');
        }
        
        // Obtener cursos del profesor para la temporada actual
        $courses = $teacher->courses()
            ->with(['season', 'category'])
            ->withCount([
                'registrations as confirmed_students_count' => function ($query) {
                    $query->whereIn('status', ['confirmed', 'completed']);
                }
            ])
            ->latest()
            ->get();
        
        return view('campus.teacher.courses', compact('courses', 'teacher'));
    }
    
    /**
     * Mostrar detalles de un curso específico
     */
    public function showCourse($courseId)
    {
        $course = CampusCourse::with(['season', 'category', 'teachers'])->findOrFail($courseId);
        
        $this->authorizeCourse($course);
        
        return view('campus.teacher.course-show', compact('course'));
    }
    
    /**
     * Mostrar estudiantes de un curso
     */
    public function students($courseId)
    {
        $course = CampusCourse::with([
            'season',
            'category',
            'registrations' => function ($query) {
                $query->whereIn('status', ['confirmed', 'completed'])
                      ->with(['student.user']);
            }
        ])->findOrFail($courseId);
        
        $this->authorizeCourse($course);
        
        $students = $course->registrations->map(function ($registration) {
            return [
                'id' => $registration->student->id,
                'name' => $registration->student->user->name ?? 
                         ($registration->student->first_name . ' ' . $registration->student->last_name),
                'email' => $registration->student->email ?? $registration->student->user->email,
                'student_code' => $registration->student->student_code,
                'registration_date' => $registration->registration_date,
                'status' => $registration->status,
                'registration_id' => $registration->id
            ];
        });
        return view('campus.teacher.students', compact('course', 'students'));
    }
    
    /**
     * Autorizar que el profesor tenga acceso al curso
     */
    private function authorizeCourse(CampusCourse $course)
    {
        $teacher = Auth::user()->teacher;
        
        // Verificar que el usuario tenga perfil de profesor
        abort_if(!$teacher, 403, 'No tienes un perfil de profesor');
        
        // Verificar que el profesor esté asignado a este curso
        $isAssigned = $course->teachers()
            ->where('campus_teachers.id', $teacher->id)
            ->exists();
            
        abort_if(!$isAssigned, 403, 'No estás asignado a este curso');
    }
}