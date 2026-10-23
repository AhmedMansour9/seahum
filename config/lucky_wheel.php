<?php

return [
    /*
     * Seeded value and fallback for the minimum order subtotal (base currency) that qualifies
     * an order for lucky wheel gifts. Admins change the live value from the dashboard.
     */
    'default_minimum_order_amount' => (string)env('LUCKY_WHEEL_DEFAULT_MINIMUM_ORDER_AMOUNT', '1000.00'),

    /*
     * Spin requests one customer may send per minute, counted apart from every other request.
     */
    'spin_attempts_per_minute' => (int)env('LUCKY_WHEEL_SPIN_ATTEMPTS_PER_MINUTE', 10),
];
