<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;

    public function __construct(User $user, string $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    public function build()
    {
        Log::info('Verification email URL: ' . $this->verificationUrl);
        
        return $this->subject('Verify Your Email Address - ' . config('app.name'))
                    ->markdown('emails.auth.verify-email')
                    ->with([
                        'userName' => $this->user->name,
                        'user' => $this->user,
                        'verificationUrl' => $this->verificationUrl,
                        'appName' => config('app.name'),
                    ]);
    }
}