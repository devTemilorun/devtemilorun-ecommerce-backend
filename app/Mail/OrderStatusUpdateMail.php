<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $previousStatus;

    public function __construct(Order $order, string $previousStatus)
    {
        $this->order          = $order;
        $this->previousStatus = $previousStatus;
    }

    public function build()
    {
        $statusLabels = [
            'pending_payment' => 'Pending Payment',
            'paid'            => 'Payment Confirmed',
            'processing'      => 'Processing',
            'shipped'         => 'Shipped',
            'delivered'       => 'Delivered',
            'cancelled'       => 'Cancelled',
            'refunded'        => 'Refunded',
        ];

        $newLabel = $statusLabels[$this->order->status] ?? ucfirst($this->order->status);

        return $this->subject("Order #{$this->order->order_number} Update: {$newLabel}")
                    ->markdown('emails.orders.status-update')
                    ->with([
                        'order'          => $this->order,
                        'user'           => $this->order->user,
                        'newStatus'      => $this->order->status,
                        'newStatusLabel' => $newLabel,
                        'previousStatus' => $this->previousStatus,
                        'appName'        => config('app.name'),
                    ]);
    }
}