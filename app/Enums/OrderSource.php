<?php

namespace App\Enums;

enum OrderSource: string
{
    case App = 'app';
    case EasyOrders = 'easy_orders';
    case Website = 'website';
    /** Typed in by customer service from the delivery app. */
    case Manual = 'manual';

    /** Translation key of the name shown to admin and staff. */
    public function labelKey(): string
    {
        return match ($this) {
            self::App => 'order_source_app',
            self::EasyOrders => 'EasyOrders',
            self::Website => 'order_source_website',
            self::Manual => 'order_source_manual',
        };
    }

    /** Arabic name used in staff push notifications. */
    public function arabicLabel(): string
    {
        return match ($this) {
            self::App => 'تطبيق',
            self::EasyOrders => 'إيزي أوردرز',
            self::Website => 'الموقع',
            self::Manual => 'أوردر يدوي',
        };
    }
}
