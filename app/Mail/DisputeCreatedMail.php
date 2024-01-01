<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisputeCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $disputeData;

    public function __construct(array $disputeData)
    {
        $this->disputeData = $disputeData;
    }

    public function build()
    {
        return $this->subject('Payment Dispute Created - Action Required')
                    ->markdown('emails.admin.dispute-created')
                    ->with([
                        'disputeData' => $this->disputeData,
                        'transaction' => $this->disputeData['transaction'] ?? [],
                        'reason' => $this->disputeData['reason'] ?? 'Unknown',
                        'amount' => $this->disputeData['amount'] ?? 0,
                        'currency' => $this->disputeData['currency'] ?? 'NGN',
                        'appName' => config('app.name'),
                    ]);
    }
}