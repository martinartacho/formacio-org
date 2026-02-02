<?php

namespace App\Http\Controllers\TeacherAccess;

use App\Http\Controllers\Controller;
use App\Models\TeacherAccessToken;
use App\Models\CampusTeacher;
use App\Models\CampusSeason;
use App\Models\CampusCourse;
use App\Models\CampusCourseTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherAccessController extends Controller
{
    public function show(string $token)
    {
        \Log::info('=== TEACHER ACCESS START ===');
        \Log::info('Token recibido:', ['token' => $token]);
        
        // 1. Buscar season i actual
        $season = CampusSeason::where('is_current', true)->first();       
       
        // 2. Buscar el token
        $accessToken = TeacherAccessToken::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$accessToken) {
            \Log::error('Token no encontrado o expirado:', ['token' => $token]);
            abort(404, 'Enlace no válido o expirado');
        }
        
        \Log::info('Token encontrado:', [
            'id' => $accessToken->id,
            'teacher_id' => $accessToken->teacher_id,
            'expires_at' => $accessToken->expires_at,
            'used_at' => $accessToken->used_at
        ]);


        // 23. Buscar el usuario (teacher_id es el ID de User según tu migración)
        $user = User::find($accessToken->teacher_id);
        
        if (!$user) {
            \Log::error('Usuario no encontrado:', ['teacher_id' => $accessToken->teacher_id]);
            abort(404, 'Usuario no encontrado');
        }
        
        \Log::info('Usuario encontrado:', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        // 4. Buscar el profesor relacionado
        $teacher = CampusTeacher::where('user_id', $user->id)->first();
        
        if (!$teacher) {
            \Log::error('CampusTeacher no encontrado para user_id:', ['user_id' => $user->id]);
            // Puedes crear uno si no existe o abortar
            abort(404, 'Perfil de profesor no encontrado');
        }
        
        \Log::info('CampusTeacher encontrado:', [
            'teacher_id' => $teacher->id,
            'first_name' => $teacher->first_name,
            'last_name' => $teacher->last_name
        ]);

        // 5. Buscar el curso relacionado
        $courseasignat = CampusCourseTeacher::where('teacher_id', $teacher->id)->first();
       

        $course = CampusCourse::where('id', $courseasignat->course_id)->first();
        // dd($courseasignat, $course);
        
        \Log::info('Cousers encontrados:', [
            'id' => $teacher->id,
            'name' => $course->name,            
        ]);

        \Log::info('=== TEACHER ACCESS END ===');

        return view('teacher-access.form', [ // Asegúrate que esta vista existe
            'token' => $accessToken,
            'user' => $user,
            'season' => $season,
            'teacher' => $teacher,
            'course' => $course,
            'courseasignat' => $courseasignat
        ]);
    }

    public function store(Request $request, string $token)
    {
        $access = TeacherAccessToken::where('token', $token)
            ->whereNull('used_at')
            ->firstOrFail();

        // v1: només marcar RGPD
        if ($request->boolean('consent_rgpd')) {
            // aquí reutilitzes storeConsent ja existent
        }

        $access->update([
            'used_at' => now(),
        ]);

        return view('teacher-access.success');
    }
}
