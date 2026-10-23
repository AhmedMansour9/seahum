<?php

namespace Tests\Feature\LuckyWheel;

use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Services\LuckyWheelService;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LuckyWheelPopupTest extends TestCase
{
    use InteractsWithLuckyWheel;

    private const POPUP_VIEW = 'web-views.seafood.partials._lucky-wheel-popup';

    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(resource_path('themes/default'));
        if (!defined('VIEW_FILE_NAMES')) {
            define('VIEW_FILE_NAMES', include resource_path('themes/default/file_names.php'));
        }

        $this->createLuckyWheelTables();
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon');
    }

    public function test_popup_invites_a_logged_in_customer_who_can_spin(): void
    {
        $this->actingAs($this->createCustomer(), 'customer');

        $popup = view(self::POPUP_VIEW)->render();

        $this->assertStringContainsString('id="sf-lw-popup"', $popup);
        $this->assertStringContainsString(route('lucky-wheel'), $popup);
    }

    public function test_popup_is_hidden_after_the_customer_spun_today(): void
    {
        $customer = $this->createCustomer();
        app(LuckyWheelService::class)->spin(customerId: $customer->id);
        $this->actingAs($customer, 'customer');

        $this->assertStringNotContainsString('sf-lw-popup', view(self::POPUP_VIEW)->render());
    }

    public function test_popup_is_hidden_for_guests_and_when_the_wheel_is_disabled(): void
    {
        $this->assertStringNotContainsString('sf-lw-popup', view(self::POPUP_VIEW)->render());

        $this->configureLuckyWheel(isEnabled: false, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->actingAs($this->createCustomer(), 'customer');
        $this->assertStringNotContainsString('sf-lw-popup', view(self::POPUP_VIEW)->render());
    }
}
