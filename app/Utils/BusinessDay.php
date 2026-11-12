<?php

namespace App\Utils;

use Carbon\Carbon;

class BusinessDay
{
    /**
     * Start and end of today in the business timezone, expressed in app.timezone so they compare correctly with stored timestamps.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function todayRange(): array
    {
        $startOfBusinessDay = Carbon::now(config('order_regions.timezone'))->startOfDay();
        $endOfBusinessDay = $startOfBusinessDay->copy()->endOfDay();

        return [
            $startOfBusinessDay->setTimezone(config('app.timezone')),
            $endOfBusinessDay->setTimezone(config('app.timezone')),
        ];
    }
}
