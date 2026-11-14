<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\DeliveryAppStaffService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualOrderRankTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_type')->default('default_type');
            $table->unsignedInteger('manual_rank')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return array<int, int>
     */
    private function createOrders(int $count): array
    {
        return array_map(fn () => DB::table('orders')->insertGetId(['order_type' => 'default_type']), range(1, $count));
    }

    /**
     * @return array<int, int>
     */
    private function rankedIds(): array
    {
        return Order::query()->manuallyRanked()->orderBy('id')->pluck('id')->all();
    }

    public function test_ranked_orders_come_first_in_rank_order_and_the_rest_keep_their_order(): void
    {
        [$first, $second, $third, $fourth] = $this->createOrders(count: 4);

        app(DeliveryAppStaffService::class)->updateManualRanks(orderedIds: [$third, $first]);

        $this->assertSame([$third, $first, $second, $fourth], $this->rankedIds());
    }

    public function test_orders_ranked_before_stay_after_the_newly_ranked_ones(): void
    {
        [$first, $second, $third, $fourth] = $this->createOrders(count: 4);
        $service = app(DeliveryAppStaffService::class);
        $service->updateManualRanks(orderedIds: [$first, $second]);

        $service->updateManualRanks(orderedIds: [$fourth]);

        $this->assertSame([$fourth, $first, $second, $third], $this->rankedIds());
        $this->assertSame([1, 2, 3], Order::whereNotNull('manual_rank')->orderBy('manual_rank')->pluck('manual_rank')->all());
    }

    public function test_removed_orders_lose_their_rank(): void
    {
        [$first, $second, $third] = $this->createOrders(count: 3);
        $service = app(DeliveryAppStaffService::class);
        $service->updateManualRanks(orderedIds: [$third, $second, $first]);

        $service->updateManualRanks(orderedIds: [], removedIds: [$third]);

        $this->assertNull(Order::find($third)->manual_rank);
        $this->assertSame([$second, $first, $third], $this->rankedIds());
    }
}
