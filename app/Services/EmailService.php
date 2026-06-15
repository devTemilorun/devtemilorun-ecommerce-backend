<?php

namespace App\Services;

use App\Mail\WelcomeMail;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeMail($user));
    }

    public function sendPasswordResetEmail(string $email, string $token): void
    {
        Mail::to($email)->send(new PasswordResetMail($token, $email));
    }
}