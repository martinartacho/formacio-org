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

    public function update(Request $request, string $token)
    {
        \Log::info('=== UPDATE START TeacherPublicProfileController ===');
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

        \Log::info('=== UPDATE COMPLETED TeacherPublicProfileController ===');

        return view('treasury.public.completed');
    }
}