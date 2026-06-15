<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject("Your Order #{$this->order->order_number} Has Been Shipped")
                    ->markdown('emails.orders.shipped')
                    ->with([
                        'order' => $this->order,
                        'trackingNumber' => $this->order->metadata['tracking_number'] ?? null,
                    ]);
    }
}