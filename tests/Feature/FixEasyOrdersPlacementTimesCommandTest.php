<?php

namespace Tests\Feature;

use App\Enums\OrderSource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FixEasyOrdersPlacementTimesCommandTest extends TestCase
{
    private string $previousTimezone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Africa/Cairo');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_source')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->dateTime('created_at')->nullable();
        });

        DB::table('orders')->insert([
            ['id' => 1, 'order_source' => OrderSource::EasyOrders->value, 'created_at' => '2026-11-09 03:55:00', 'updated_at' => '2026-11-09 00:10:00'],
            ['id' => 2, 'order_source' => OrderSource::EasyOrders->value, 'created_at' => '2026-10-20 16:00:00', 'updated_at' => '2026-11-09 00:20:00'],
            ['id' => 3, 'order_source' => OrderSource::EasyOrders->value, 'created_at' => '2026-11-08 19:11:00', 'updated_at' => '2026-11-08 19:11:00'],
            ['id' => 4, 'order_source' => null, 'created_at' => '2026-11-09 00:15:00', 'updated_at' => '2026-11-09 00:15:00'],
        ]);
        DB::table('order_details')->insert([
            ['order_id' => 1, 'created_at' => '2026-11-09 00:10:00'],
            ['order_id' => 2, 'created_at' => '2026-11-09 00:20:00'],
            ['order_id' => 3, 'created_at' => '2026-11-08 19:11:00'],
            ['order_id' => 4, 'created_at' => '2026-11-09 00:15:00'],
        ]);
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->previousTimezone);

        parent::tearDown();
    }

    public function test_orders_stored_in_the_period_are_converted_from_dhaka_to_the_store_timezone(): void
    {
        $this->artisan('easy-orders:fix-placement-times', ['--stored-from' => '2026-11-08 22:46:30', '--stored-to' => '2026-11-09 01:04:45'])
            ->assertSuccessful();

        $this->assertSame('2026-11-08 23:55:00', DB::table('orders')->where('id', 1)->value('created_at'));
        $this->assertSame('2026-10-20 13:00:00', DB::table('orders')->where('id', 2)->value('created_at'));
        $this->assertSame('2026-11-08 19:11:00', DB::table('orders')->where('id', 3)->value('created_at'));
        $this->assertSame('2026-11-09 00:15:00', DB::table('orders')->where('id', 4)->value('created_at'));
        $this->assertSame('2026-11-09 00:10:00', DB::table('orders')->where('id', 1)->value('updated_at'));
    }

    public function test_dry_run_changes_nothing(): void
    {
        $this->artisan('easy-orders:fix-placement-times', ['--stored-from' => '2026-11-08 22:46:30', '--stored-to' => '2026-11-09 01:04:45', '--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame('2026-11-09 03:55:00', DB::table('orders')->where('id', 1)->value('created_at'));
    }

    public function test_both_period_options_are_required(): void
    {
        $this->artisan('easy-orders:fix-placement-times', ['--stored-from' => '2026-11-08 22:46:30'])->assertFailed();
    }
}
