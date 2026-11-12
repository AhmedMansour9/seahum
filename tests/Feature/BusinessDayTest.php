<?php

namespace Tests\Feature;

use App\Utils\BusinessDay;
use Carbon\Carbon;
use Tests\TestCase;

class BusinessDayTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_today_range_follows_cairo_day_expressed_in_app_timezone(): void
    {
        config(['app.timezone' => 'Asia/Dhaka']);
        Carbon::setTestNow(Carbon::parse('2026-11-11 22:30:00', 'Africa/Cairo'));

        [$start, $end] = BusinessDay::todayRange();

        $this->assertSame('2026-11-11 04:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-12 03:59:59', $end->format('Y-m-d H:i:s'));
    }

    public function test_an_order_created_at_nine_pm_cairo_still_belongs_to_cairo_today(): void
    {
        config(['app.timezone' => 'Asia/Dhaka']);
        Carbon::setTestNow(Carbon::parse('2026-11-11 22:30:00', 'Africa/Cairo'));
        $createdAtInAppTimezone = Carbon::parse('2026-11-11 21:00:00', 'Africa/Cairo')->setTimezone('Asia/Dhaka');

        [$start, $end] = BusinessDay::todayRange();

        $this->assertTrue($createdAtInAppTimezone->between($start, $end));
    }
}
