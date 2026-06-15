<?php

namespace App\Domain\Auth\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::CUSTOMER => 'Customer',
        };
    }
}