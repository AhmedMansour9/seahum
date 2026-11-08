<?php

namespace Tests\Feature;

use App\Enums\OrderSource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurgeCanceledEasyOrdersCommandTest extends TestCase
{
    private const RELATED_TABLES = [
        'order_details',
        'order_details_rewards',
        'order_status_histories',
        'order_edit_histories',
        'order_transactions',
        'order_expected_delivery_histories',
        'delivery_histories',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_source')->nullable();
            $table->string('order_status')->nullable();
        });
        foreach (self::RELATED_TABLES as $relatedTable) {
            Schema::create($relatedTable, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
            });
        }

        DB::table('orders')->insert([
            ['id' => 1, 'order_source' => OrderSource::EasyOrders->value, 'order_status' => 'canceled'],
            ['id' => 2, 'order_source' => OrderSource::EasyOrders->value, 'order_status' => 'pending'],
            ['id' => 3, 'order_source' => null, 'order_status' => 'canceled'],
        ]);
        foreach (self::RELATED_TABLES as $relatedTable) {
            DB::table($relatedTable)->insert([['order_id' => 1], ['order_id' => 2], ['order_id' => 3]]);
        }
    }

    public function test_dry_run_deletes_nothing(): void
    {
        $this->artisan('easy-orders:purge-canceled', ['--dry-run' => true])
            ->expectsOutput('1 canceled EasyOrders orders found.')
            ->assertSuccessful();

        $this->assertSame(3, DB::table('orders')->count());
    }

    public function test_only_canceled_easy_orders_are_deleted_with_their_related_rows(): void
    {
        $this->artisan('easy-orders:purge-canceled', ['--force' => true])->assertSuccessful();

        $this->assertSame([2, 3], DB::table('orders')->orderBy('id')->pluck('id')->all());
        foreach (self::RELATED_TABLES as $relatedTable) {
            $this->assertSame([2, 3], DB::table($relatedTable)->orderBy('order_id')->pluck('order_id')->all(), $relatedTable);
        }
    }

    public function test_declining_the_confirmation_deletes_nothing(): void
    {
        $this->artisan('easy-orders:purge-canceled')
            ->expectsConfirmation('Delete them permanently? This cannot be undone.', 'no')
            ->assertFailed();

        $this->assertSame(3, DB::table('orders')->count());
    }
}
