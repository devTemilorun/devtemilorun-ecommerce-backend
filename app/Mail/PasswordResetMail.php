<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->markdown('emails.auth.password-reset')
                    ->with([
                        'resetUrl' => config('app.frontend_url') . "/reset-password?token={$this->token}&email={$this->email}",
                    ]);
    }
}