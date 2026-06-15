<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject("Order Confirmation #{$this->order->order_number}")
                    ->markdown('emails.orders.confirmation')
                    ->with([
                        'order' => $this->order,
                        'user' => $this->order->user,
                        'items' => $this->order->items,
                    ]);
    }
}