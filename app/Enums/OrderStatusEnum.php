<?php

namespace App\Domain\Order\Enums;

enum OrderStatusEnum: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING_PAYMENT => 'Pending Payment',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING_PAYMENT => 'yellow',
            self::PAID => 'blue',
            self::PROCESSING => 'purple',
            self::SHIPPED => 'orange',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'gray',
        };
    }
}