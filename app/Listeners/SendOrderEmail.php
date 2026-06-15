<?php

namespace App\Listeners;

use App\Domain\Order\Events\OrderCreated;
use App\Jobs\SendOrderConfirmationEmail;

class SendOrderEmail
{
    public function handle(OrderCreated $event): void
    {
        dispatch(new SendOrderConfirmationEmail($event->order));
    }
}