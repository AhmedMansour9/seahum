<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationCheckMiddleware;
use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\DeliveryManDevice;
use App\Traits\PushNotificationTrait;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeliveryManMultiDeviceTest extends TestCase
{
    private const LOGIN_ENDPOINT = '/api/v2/delivery-man/auth/login';

    private const FCM_TOKEN_ENDPOINT = '/api/v2/delivery-man/update-fcm-token';

    private const LANGUAGE_ENDPOINT = '/api/v2/delivery-man/language-change';

    private const PHONE = '501234567';

    private const PASSWORD = '12345678';

    private const FCM_CLIENT_EMAIL = 'push@seahum.test';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->string('f_name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('auth_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('app_language')->nullable();
            $table->timestamps();
        });
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('data_type');
            $table->unsignedBigInteger('data_id');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
        });
    }

    public function test_migration_keeps_existing_sessions_and_push_tokens(): void
    {
        $legacyDeliveryManId = $this->createDeliveryMan(authToken: str_repeat('a', 50), fcmToken: 'legacy-fcm');
        $this->createDeliveryMan(phone: '509999999');

        $this->runDevicesMigration();

        $device = DeliveryManDevice::sole();
        $this->assertSame([$legacyDeliveryManId, str_repeat('a', 50), 'legacy-fcm'], [$device->delivery_man_id, $device->auth_token, $device->fcm_token]);
    }

    public function test_logging_in_on_a_second_device_keeps_the_first_device_logged_in(): void
    {
        $this->runDevicesMigration();
        $this->withoutMiddleware(ActivationCheckMiddleware::class);
        $deliveryManId = $this->createDeliveryMan();

        $firstToken = $this->login();
        $secondToken = $this->login();

        $this->assertNotSame($firstToken, $secondToken);
        $this->assertSame(2, DeliveryManDevice::where('delivery_man_id', $deliveryManId)->count());
        $this->putJson(self::LANGUAGE_ENDPOINT, ['current_language' => 'en'], ['Authorization' => "Bearer $firstToken"])->assertOk();
        $this->putJson(self::LANGUAGE_ENDPOINT, ['current_language' => 'en'], ['Authorization' => "Bearer $secondToken"])->assertOk();
        $this->putJson(self::LANGUAGE_ENDPOINT, ['current_language' => 'en'], ['Authorization' => 'Bearer '.str_repeat('x', 50)])->assertUnauthorized();
    }

    public function test_push_token_is_stored_per_device_and_a_relogin_on_the_same_install_replaces_its_old_session(): void
    {
        $this->runDevicesMigration();
        $this->withoutMiddleware(ActivationCheckMiddleware::class);
        $deliveryManId = $this->createDeliveryMan();
        $phoneToken = $this->login();
        $tabletToken = $this->login();

        $this->putJson(self::FCM_TOKEN_ENDPOINT, ['fcm_token' => 'phone-fcm'], ['Authorization' => "Bearer $phoneToken"])->assertOk();
        $this->putJson(self::FCM_TOKEN_ENDPOINT, ['fcm_token' => 'tablet-fcm'], ['Authorization' => "Bearer $tabletToken"])->assertOk();

        $this->assertEqualsCanonicalizing(['phone-fcm', 'tablet-fcm'], DeliveryManDevice::pluck('fcm_token')->all());
        $this->assertSame('tablet-fcm', DeliveryMan::find($deliveryManId)->fcm_token);

        $phoneReloginToken = $this->login();
        $this->putJson(self::FCM_TOKEN_ENDPOINT, ['fcm_token' => 'phone-fcm'], ['Authorization' => "Bearer $phoneReloginToken"])->assertOk();

        $this->assertEqualsCanonicalizing([$tabletToken, $phoneReloginToken], DeliveryManDevice::pluck('auth_token')->all());
    }

    public function test_push_goes_once_to_every_device_and_unregistered_tokens_are_dropped(): void
    {
        $this->runDevicesMigration();
        $this->configureFcm();
        $deliveryMan = DeliveryMan::find($this->createDeliveryMan());
        $otherDeliveryMan = DeliveryMan::find($this->createDeliveryMan(phone: '509999999'));
        $deliveryMan->devices()->createMany([
            ['auth_token' => str_repeat('a', 50), 'fcm_token' => 'phone-fcm'],
            ['auth_token' => str_repeat('b', 50), 'fcm_token' => 'tablet-fcm'],
            ['auth_token' => str_repeat('c', 50), 'fcm_token' => 'dead-fcm'],
            ['auth_token' => str_repeat('d', 50), 'fcm_token' => null],
        ]);
        $otherDeliveryMan->devices()->create(['auth_token' => str_repeat('e', 50), 'fcm_token' => 'other-fcm']);
        Http::fake(function (HttpRequest $request) {
            return $request['message']['token'] === 'dead-fcm'
                ? Http::response(['error' => ['code' => 404, 'status' => 'NOT_FOUND']], 404)
                : Http::response(['name' => 'projects/seahum/messages/1']);
        });

        $this->pushSender()->sendToDeliveryMan(deliveryMan: $deliveryMan, data: ['title' => 'order', 'description' => 'New order assigned', 'order_id' => 100022, 'image' => '', 'type' => 'order']);

        $sentTokens = Http::recorded()->map(fn (array $exchange) => $exchange[0]['message']['token'])->all();
        $this->assertEqualsCanonicalizing(['phone-fcm', 'tablet-fcm', 'dead-fcm'], $sentTokens);
        $this->assertNull(DeliveryManDevice::where('auth_token', str_repeat('c', 50))->value('fcm_token'));
        $this->assertSame('phone-fcm', DeliveryManDevice::where('auth_token', str_repeat('a', 50))->value('fcm_token'));
    }

    public function test_push_is_skipped_without_crashing_when_fcm_is_not_configured(): void
    {
        $this->runDevicesMigration();
        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect());
        Http::fake();
        $deliveryMan = DeliveryMan::find($this->createDeliveryMan());
        $deliveryMan->devices()->create(['auth_token' => str_repeat('a', 50), 'fcm_token' => 'phone-fcm']);

        $this->pushSender()->sendToDeliveryMan(deliveryMan: $deliveryMan, data: ['title' => 'order', 'description' => 'New order assigned', 'order_id' => 100022, 'image' => '', 'type' => 'order']);

        Http::assertNothingSent();
    }

    private function runDevicesMigration(): void
    {
        (require base_path('database/migrations/2026_10_29_002728_create_delivery_man_devices_table.php'))->up();
    }

    private function createDeliveryMan(string $phone = self::PHONE, ?string $authToken = null, ?string $fcmToken = null): int
    {
        return DB::table('delivery_men')->insertGetId([
            'f_name' => 'Omar',
            'country_code' => '+971',
            'phone' => $phone,
            'password' => Hash::make(self::PASSWORD),
            'auth_token' => $authToken,
            'fcm_token' => $fcmToken,
        ]);
    }

    private function login(): string
    {
        return $this->postJson(self::LOGIN_ENDPOINT, ['phone' => self::PHONE, 'country_code' => '+971', 'password' => self::PASSWORD])
            ->assertOk()
            ->json('token');
    }

    private function configureFcm(): void
    {
        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect([
            new BusinessSetting(['type' => 'push_notification_key', 'value' => json_encode(['project_id' => 'seahum', 'client_email' => self::FCM_CLIENT_EMAIL])]),
        ]));
        Cache::put('fcm_access_token_'.md5(self::FCM_CLIENT_EMAIL), 'fake-access-token');
    }

    private function pushSender(): object
    {
        return new class {
            use PushNotificationTrait;

            public function sendToDeliveryMan(DeliveryMan $deliveryMan, array $data): void
            {
                $this->sendPushNotificationToDeliveryManDevices(deliveryMan: $deliveryMan, data: $data);
            }
        };
    }
}
