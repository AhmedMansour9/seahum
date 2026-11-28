<?php

namespace Tests\Feature;

use App\Models\Builders\OrderQueryBuilder;
use App\Models\Order;
use App\Services\OrderActivityLogService;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Admin must see who changed any important order field. Most of the code updates orders with
 * Order::where(...)->update([...]) instead of model saves, so the logging lives in the order query
 * builder, which both paths go through.
 */
class OrderActivityLogTest extends TestCase
{
    public function test_every_order_update_goes_through_the_logging_builder(): void
    {
        $this->assertInstanceOf(OrderQueryBuilder::class, Order::query());
        $this->assertInstanceOf(OrderQueryBuilder::class, Order::where('id', 1));

        $builder = file_get_contents(app_path('Models/Builders/OrderQueryBuilder.php'));
        $this->assertStringContainsString('$ordersBefore = (clone $this)->toBase()->select($columns)->get();', $builder, 'Old values are read before the update.');
        $this->assertStringContainsString('$activityLog->recordChanges(ordersBefore: $ordersBefore, values: $trackedValues);', $builder);
    }

    public function test_no_order_update_bypasses_the_model_query_builder(): void
    {
        $offenders = [];
        foreach (['app', 'Modules'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($directory)));
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), 'FixEasyOrdersPlacementTimesCommand')) {
                    continue;
                }
                if (preg_match("/DB::table\('orders'\)[^;]*->update\(/s", file_get_contents($file->getPathname()))) {
                    $offenders[] = $file->getPathname();
                }
            }
        }

        $this->assertSame([], $offenders, 'DB::table(\'orders\')->update() skips the change log; use Order::where(...)->update().');
    }

    public function test_only_tracked_fields_are_logged(): void
    {
        $service = new OrderActivityLogService;

        $this->assertSame(['order_status', 'payment_status', 'delivery_man_id'], $service->trackedFieldsIn([
            'order_status' => 'delivered',
            'payment_status' => 'paid',
            'delivery_man_id' => 9,
            'manual_rank' => 3,
            'checked' => 1,
            'updated_at' => now(),
        ]));
    }

    public function test_the_actor_is_known_for_admin_delivery_app_vendor_and_customer_requests(): void
    {
        $service = file_get_contents(app_path('Services/OrderActivityLogService.php'));

        foreach (["Auth::guard('admin')->user()", "\$request->get('delivery_man')", "Auth::guard('seller')->user()", "Auth::guard('customer')->user()"] as $actorSource) {
            $this->assertStringContainsString($actorSource, $service);
        }
        foreach (["'admin_panel'", "'delivery_app'", "'vendor_app'", "'customer_app'", "'website'"] as $source) {
            $this->assertStringContainsString($source, $service);
        }
        $this->assertStringContainsString('report($exception);', $service, 'A logging failure must not break the order update.');
    }

    public function test_admin_order_details_shows_the_change_log_with_labels(): void
    {
        $detailsPage = file_get_contents(resource_path('views/admin-views/order/order-details.blade.php'));
        $this->assertStringContainsString("@include('admin-views.order.partials._activity-log', ['order' => \$order])", $detailsPage);

        $partial = file_get_contents(resource_path('views/admin-views/order/partials/_activity-log.blade.php'));
        $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
        file_put_contents($compiledFile, Blade::compileString($partial));
        exec('"'.PHP_BINARY.'" -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
        unlink($compiledFile);
        $this->assertSame(0, $exitCode, implode("\n", $output));

        foreach (['en', 'eg'] as $locale) {
            $messages = include base_path("resources/lang/{$locale}/new-messages.php");
            foreach (OrderActivityLogService::TRACKED_FIELDS as $field) {
                $this->assertArrayHasKey('order_activity_field_'.$field, $messages, "{$locale} is missing the label for {$field}.");
            }
            foreach (['admin', 'vendor', 'delivery_man', 'customer', 'guest', 'system'] as $actor) {
                $this->assertArrayHasKey('order_activity_actor_'.$actor, $messages);
            }
            foreach (['admin_panel', 'vendor_panel', 'delivery_app', 'vendor_app', 'customer_app', 'website', 'system'] as $source) {
                $this->assertArrayHasKey('order_activity_source_'.$source, $messages);
            }
        }
    }
}
