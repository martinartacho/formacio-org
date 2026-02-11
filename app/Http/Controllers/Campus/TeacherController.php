<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function __construct()
    {
        // Métodos CRUD para administración
        $this->middleware('can:campus.teachers.index')->only(['index']);
        $this->middleware('can:campus.teachers.create')->only(['create', 'store']);
        $this->middleware('can:campus.teachers.edit')->only(['edit', 'update']);
        $this->middleware('can:campus.teachers.delete')->only(['destroy']);
        $this->middleware('can:campus.teachers.view')->only(['show']);
        
        // Métodos para profesores autenticados (mantener existentes)
        $this->middleware('auth')->only(['courses', 'showCourse', 'students']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = CampusTeacher::with(['user', 'courses'])
            ->orderBy('last_name')
            ->paginate(15);
            
        return view('campus.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = CampusCourse::orderBy('title')->get();
        return view('campus.teachers.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'dni' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:34',
            'bank_titular' => 'nullable|string|max:255',
            'fiscal_situation' => 'nullable|string|in:autonomo,empleat,pensionista,altres',
            'degree' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'areas' => 'nullable|string',
            'hiring_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,on_leave',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:campus_courses,id',
            'hours_assigned' => 'nullable|array',
            'hours_assigned.*' => 'nullable|integer|min:1',
            'role' => 'nullable|array',
            'role.*' => 'nullable|string|in:teacher,assistant',
        ]);

        // Generar código de profesor único
        $teacherCode = 'PROF_' . str_pad(CampusTeacher::count() + 1, 4, '0', STR_PAD_LEFT);

        // Crear usuario
        $user = \App\Models\User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt('password123'), // Contraseña temporal
        ]);
        
        // Asignar rol de profesor
        $user->assignRole('teacher');

        // Crear profesor
        $teacher = CampusTeacher::create([
            'user_id' => $user->id,
            'teacher_code' => $teacherCode,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'bank_titular' => $validated['bank_titular'] ?? null,
            'fiscal_id' => $validated['fiscal_id'] ?? null,
            'fiscal_situation' => $validated['fiscal_situation'] ?? null,
            'degree' => $validated['degree'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'title' => $validated['title'] ?? null,
            'areas' => $validated['areas'] ?? null,
            'hiring_date' => $validated['hiring_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        // Asignar cursos si se proporcionaron
        if (!empty($validated['courses'])) {
            foreach ($validated['courses'] as $index => $courseId) {
                $teacher->courses()->attach($courseId, [
                    'hours_assigned' => $validated['hours_assigned'][$index] ?? 0,
                    'role' => $validated['role'][$index] ?? 'teacher',
                ]);
            }
        }

        return redirect()->route('campus.teachers.show', $teacher)
            ->with('success', 'Professor creat correctament.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CampusTeacher $teacher)
    {
        $teacher->load(['user', 'courses' => function($query) {
            $query->with(['season', 'category']);
        }]);
        
        return view('campus.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CampusTeacher $teacher)
    {
        $teacher->load(['courses']);
        $courses = CampusCourse::orderBy('title')->get();
        
        // Verificar si hay pagos confirmados para los cursos asignados
        $restrictedCourses = [];
        foreach ($teacher->courses as $course) {
            $currentSeason = $course->season_id ?? null;
            if ($currentSeason) {
                $payment = CampusTeacherPayment::where('teacher_id', $teacher->id)
                    ->where('course_id', $course->id)
                    ->where('season_id', $currentSeason)
                    ->whereNotNull('payment_option')
                    ->first();
                
                if ($payment) {
                    $restrictedCourses[] = $course->id;
                }
            }
        }
        
        return view('campus.teachers.edit', compact('teacher', 'courses', 'restrictedCourses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CampusTeacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($teacher->user_id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'dni' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:34',
            'bank_titular' => 'nullable|string|max:255',
            'fiscal_id' => 'nullable|string|max:20',
            'fiscal_situation' => 'nullable|string|max:50',
            'degree' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:50',
            'areas' => 'nullable|string|max:1000',
            'hiring_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,on_leave',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:campus_courses,id',
            'hours_assigned' => 'nullable|array',
            'hours_assigned.*' => 'nullable|integer|min:1',
            'role' => 'nullable|array',
            'role.*' => 'nullable|string|in:teacher,assistant',
        ]);

        // Actualizar usuario
        $teacher->user->update([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
        ]);

        // Actualizar profesor
        $teacher->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'bank_titular' => $validated['bank_titular'] ?? null,
            'fiscal_id' => $validated['fiscal_id'] ?? null,
            'fiscal_situation' => $validated['fiscal_situation'] ?? null,
            'degree' => $validated['degree'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'title' => $validated['title'] ?? null,
            'areas' => $validated['areas'] ? explode(',', str_replace(' ', '', $validated['areas'])) : null,
            'hiring_date' => $validated['hiring_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        // Verificar cursos con pagos confirmados y excluirlos de la actualización
        $restrictedCourses = [];
        if (!empty($validated['courses'])) {
            foreach ($validated['courses'] as $index => $courseId) {
                $course = CampusCourse::find($courseId);
                if ($course) {
                    $currentSeason = $course->season_id ?? null;
                    if ($currentSeason) {
                        $payment = CampusTeacherPayment::where('teacher_id', $teacher->id)
                            ->where('course_id', $courseId)
                            ->where('season_id', $currentSeason)
                            ->whereNotNull('payment_option')
                            ->first();
                        
                        if ($payment) {
                            $restrictedCourses[] = $courseId;
                        }
                    }
                }
            }
        }

        // Sincronizar cursos solo los que no están restringidos
        if (!empty($validated['courses'])) {
            $syncData = [];
            foreach ($validated['courses'] as $index => $courseId) {
                if (!in_array($courseId, $restrictedCourses)) {
                    $syncData[$courseId] = [
                        'hours_assigned' => $validated['hours_assigned'][$index] ?? 0,
                        'role' => $validated['role'][$index] ?? 'teacher',
                    ];
                }
            }
            
            // Obtener cursos actuales que no están restringidos
            $currentUnrestrictedCourses = $teacher->courses()
                ->whereNotIn('campus_courses.id', $restrictedCourses)
                ->pluck('campus_courses.id')
                ->toArray();
            
            // Sincronizar solo cursos no restringidos
            $teacher->courses()->sync($syncData);
            
            // Mantener cursos restringidos sin cambios
            foreach ($restrictedCourses as $restrictedCourseId) {
                if (!$teacher->courses()->where('campus_courses.id', $restrictedCourseId)->exists()) {
                    // Si por alguna razón no existe, volver a añadirlo con datos originales
                    $originalPivot = $teacher->courses()
                        ->withPivot(['hours_assigned', 'role'])
                        ->where('campus_courses.id', $restrictedCourseId)
                        ->first();
                    
                    if ($originalPivot) {
                        $teacher->courses()->attach($restrictedCourseId, [
                            'hours_assigned' => $originalPivot->pivot->hours_assigned,
                            'role' => $originalPivot->pivot->role,
                        ]);
                    }
                }
            }
        } else {
            // Si no se envían cursos, eliminar solo los no restringidos
            $teacher->courses()->whereNotIn('campus_courses.id', $restrictedCourses)->detach();
        }

        return redirect()->route('campus.teachers.show', $teacher)
            ->with('success', 'Professor actualitzat correctament.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CampusTeacher $teacher)
    {
        // Eliminar usuario asociado
        $teacher->user->delete();
        
        // Eliminar profesor
        $teacher->delete();

        return redirect()->route('campus.teachers.index')
            ->with('success', 'Professor eliminat correctament.');
    }

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