<?php

return [
    /*
     * Automatic discount for an order that holds a product of the anchor category together with
     * at least one product of any other category (e.g. caviar + anything else = 200 off).
     */
    'enabled' => (bool)env('CATEGORY_COMBO_DISCOUNT_ENABLED', true),

    'anchor_category_id' => (int)env('CATEGORY_COMBO_DISCOUNT_CATEGORY_ID', 16),

    /*
     * Flat amount in the base currency, taken once per order.
     */
    'amount' => (float)env('CATEGORY_COMBO_DISCOUNT_AMOUNT', 200),
];
