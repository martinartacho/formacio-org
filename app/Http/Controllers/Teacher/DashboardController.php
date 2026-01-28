<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('teacher.dashboard', [
            // reutilitza exactament el que ja tenies
            'teacher' => $user->teacher ?? null,
            'season' => null,
            'seasons' => [],
            'teacherCourses' => collect(),
            'stats' => [],
            'debug' => null,
            'error' => null,
        ]);
    }
}
