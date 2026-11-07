<?php

namespace App\Enums;

enum DeliveryManAppRole: string
{
    case DeliveryMan = 'delivery_man';
    case CustomerService = 'customer_service';
    case Admin = 'admin';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isStaff(): bool
    {
        return $this !== self::DeliveryMan;
    }
}
