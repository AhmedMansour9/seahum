<?php

namespace App\Enums;

enum PaymentMethod
{
    const CASH_ON_DELIVERY = 'cash_on_delivery';
    const CARD_ON_DELIVERY = 'card_on_delivery';

    /**
     * Methods collected by the delivery agent when the order is delivered (cash or POS card).
     */
    const PAY_ON_DELIVERY = [
        self::CASH_ON_DELIVERY,
        self::CARD_ON_DELIVERY,
    ];
}
