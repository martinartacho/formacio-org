<?php

namespace App\Http\Controllers\TeacherAccess;

use App\Http\Controllers\Controller;
use App\Mail\TeacherAccessMail;
use App\Models\User;
use App\Models\TeacherAccessToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class SendTeacherAccessController extends Controller
{
    public function send(User $teacher)
    {
        $this->authorize('campus.consents.request');

        $token = TeacherAccessToken::create([
            'teacher_id' => $teacher->id,
            'token' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($teacher->email)->send(
            new TeacherAccessMail($teacher, $token)
        );

        return back()->with('success', 'Recordatori enviat correctament.');
    }
}
