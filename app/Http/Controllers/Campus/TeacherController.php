<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function courses()
    {
        $teacher = auth()->user()->teacher;

        abort_if(!$teacher, 403);

        $courses = CampusCourse::where('teacher_id', $teacher->id)
            ->withCount([
                'registrations as students_count' => function ($q) {
                    $q->where('status', 'approved');
                }
            ])
            ->get();

        return view('campus.teacher.courses.index', compact('courses'));
    }


    public function showCourse(CampusCourse  $course)
    {
        $this->authorizeCourse($course);

        return view('campus.teacher.courses.show', compact('course'));
    }

    public function students(CampusCourse  $course)
    {
        $this->authorizeCourse($course);

        $students = $course->registrations()
            ->where('status', 'approved')
            ->with('student.user')
            ->get();

        return view('campus.teacher.courses.students', compact('course', 'students'));
    }

    private function authorizeCourse(Course $course)
    {
        $teacher = Auth::user()->teacher;

        abort_if(!$teacher || $course->teacher_id !== $teacher->id, 403);
    }
}
