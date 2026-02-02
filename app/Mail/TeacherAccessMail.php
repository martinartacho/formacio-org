<?php

namespace App\Mail;

use App\Models\User;
use App\Models\TeacherAccessToken;
use Illuminate\Mail\Mailable;

class TeacherAccessMail extends Mailable
{
    public function __construct(
        public User $teacher,
        public TeacherAccessToken $token
    ) {}

    public function build()
    {
        return $this->subject('Accés per completar consentiments')
            ->view('emails.teacher-access');
    }
}
