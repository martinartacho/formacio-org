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
public function index() {
    $user = auth()->user(); 
    $data = []; 
    if ($user->hasRole('admin')) 
    { 
        $data = app(\App\Services\Dashboard\AdminDashboardData::class)->build(); 
    } 
    
    if ($user->hasRole('teacher')) 
    {  
        $data = app(\App\Services\Dashboard\TeacherDashboardData::class)->build($user); 
    } 

    if ($user->hasRole('student')) 
    { 
        
        $data = app(\App\Services\Dashboard\StudentDashboardData::class)->build($user); 
    }

    return view('dashboard', $data); 
    }
}
