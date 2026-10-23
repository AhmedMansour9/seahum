<?php

return [
    /*
     * Seeded value and fallback for the minimum order subtotal (base currency) that qualifies
     * an order for lucky wheel gifts. Admins change the live value from the dashboard.
     */
    'default_minimum_order_amount' => (string)env('LUCKY_WHEEL_DEFAULT_MINIMUM_ORDER_AMOUNT', '1000.00'),
];
