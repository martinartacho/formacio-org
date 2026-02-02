<?php

namespace App\Http\Controllers\Public;

use App\Models\TeacherAccessToken;
use App\Models\CampusTeacher;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherPublicProfileController extends Controller
{
    /*     
    public function edit(string $token)
    {
        \Log::info('=== TEACHER ACCESS START ===');
        \Log::info('Token recibido:', ['token' => $token]);
        
        // 1. Buscar el token
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

        // 2. Buscar el usuario (teacher_id es el ID de User según tu migración)
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

        // 3. Buscar el profesor relacionado
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

        \Log::info('=== TEACHER ACCESS END ===');

        return view('teacher-access.form', [ // Asegúrate que esta vista existe
            'token' => $accessToken,
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }
    */

/*     public function edit(string $token)
    {
        // DEBUG: Forzar mostrar datos
        echo "<pre>";
        echo "=== DEBUG MODE ===\n";
        echo "Token recibido: $token\n\n";
        
        // 1. Buscar token
        $accessToken = TeacherAccessToken::where('token', $token)->first();
        
        if (!$accessToken) {
            echo "ERROR: Token no encontrado en DB\n";
            $allTokens = TeacherAccessToken::all();
            echo "Tokens en DB:\n";
            print_r($allTokens->toArray());
            die();
        }
        
        echo "Token encontrado:\n";
        print_r($accessToken->toArray());
        echo "\n";
        
        // 2. Buscar User
        $user = User::find($accessToken->teacher_id);
        
        if (!$user) {
            echo "ERROR: User no encontrado con ID: {$accessToken->teacher_id}\n";
            $allUsers = User::all();
            echo "Users en DB:\n";
            print_r($allUsers->toArray());
            die();
        }
        
        echo "User encontrado:\n";
        print_r($user->toArray());
        echo "\n";
        
        // 3. Buscar CampusTeacher
        $teacher = CampusTeacher::where('user_id', $user->id)->first();
        
        if (!$teacher) {
            echo "WARNING: CampusTeacher no encontrado para user_id: {$user->id}\n";
            echo "Creando objeto temporal...\n";
            $teacher = (object)[
                'id' => null,
                'first_name' => explode(' ', $user->name)[0] ?? '',
                'last_name' => explode(' ', $user->name, 2)[1] ?? '',
                'dni' => '',
                'email' => $user->email
            ];
        } else {
            echo "CampusTeacher encontrado:\n";
            print_r($teacher->toArray());
        }
        
        echo "</pre>";
        
        return view('teacher-access.form', [
            'token' => $accessToken,
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }
 */
    public function update(Request $request, string $token)
    {
        \Log::info('=== UPDATE START ===');
        \Log::info('Token:', ['token' => $token]);
        
        $accessToken = TeacherAccessToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Obtener usuario y profesor
        $user = User::findOrFail($accessToken->teacher_id);
        $teacher = CampusTeacher::where('user_id', $user->id)->firstOrFail();

        // VALIDACIONES
        $data = $request->validate([
            'email' => ['required', 'email'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'consent_rgpd' => ['required', 'accepted'],
            // 'needs_payment' => ['nullable', 'boolean'],
            'dni' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'iban' => ['nullable', 'string'],
            'bank_holder' => ['nullable', 'string'],
        ]);

        \Log::info('Datos validados:', $data);

        // Actualizar profesor
        $teacher->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'dni' => $data['dni'] ?? null,
        ]);

        // Actualizar usuario
        $user->update([
            'email' => $data['email'],
            'name' => $data['first_name'] . ' ' . $data['last_name'],
        ]);

        // Marcar token como usado
        $accessToken->update(['used_at' => now()]);

        \Log::info('=== UPDATE COMPLETED ===');

        return view('treasury.public.completed');
    }
}