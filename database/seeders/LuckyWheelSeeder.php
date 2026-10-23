<?php

namespace Database\Seeders;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Models\BusinessSetting;
use App\Models\LuckyWheelReward;
use App\Models\Translation;
use App\Services\LuckyWheelService;
use Database\Seeders\Concerns\PublishesSeedImages;
use Illuminate\Database\Seeder;

/**
 * Creates the lucky wheel settings (disabled, daily, configured minimum order amount) and the five initial outcomes.
 * Safe to re-run: existing settings are kept, and rewards are only seeded while the rewards
 * table is empty (soft-deleted rows included), so admin edits are never overwritten.
 */
class LuckyWheelSeeder extends Seeder
{
    use PublishesSeedImages;

    private const SEED_IMAGE_DIRECTORY = 'assets/front-end/img/seafood/products';

    private const REWARDS = [
        ['en' => '50g Salmon', 'ar' => '٥٠ جرام سمك سلمون', 'type' => LuckyWheelRewardType::Gift, 'image' => 'salmon-and-white-fish-fillets.webp'],
        ['en' => '100g Salmon Trimmings', 'ar' => '١٠٠ جرام ترينش سلمون', 'type' => LuckyWheelRewardType::Gift, 'image' => 'salmon-and-white-fish-fillets.webp'],
        ['en' => '200g Minced Salmon', 'ar' => '٢٠٠ جرام سلمون مفروم', 'type' => LuckyWheelRewardType::Gift, 'image' => 'salmon-and-white-fish-fillets.webp'],
        ['en' => '250g Shrimp', 'ar' => 'ربع كيلو جمبري', 'type' => LuckyWheelRewardType::Gift, 'image' => 'shrimp-on-ice.webp'],
        ['en' => 'Try Again Tomorrow', 'ar' => 'حاول مرة أخرى غدًا', 'type' => LuckyWheelRewardType::NoPrize, 'image' => null],
    ];

    public function run(): void
    {
        $this->createSettingIfMissing(type: LuckyWheelService::STATUS_SETTING, value: '0');
        $this->createSettingIfMissing(type: LuckyWheelService::SPIN_FREQUENCY_SETTING, value: LuckyWheelSpinFrequency::Daily->value);
        $this->createSettingIfMissing(type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING, value: config('lucky_wheel.default_minimum_order_amount'));

        if (!LuckyWheelReward::withTrashed()->exists()) {
            $this->createRewards(arabicLocale: SeafoodCatalogSeeder::resolveArabicLocale());
        }

        clearWebConfigCacheKeys();
    }

    private function createRewards(string $arabicLocale): void
    {
        foreach (self::REWARDS as $displayOrder => $reward) {
            $savedReward = LuckyWheelReward::create([
                'name' => $reward['en'],
                'type' => $reward['type'],
                'image' => $this->publishSeedImage(
                    fileName: $reward['image'],
                    sourceDirectory: self::SEED_IMAGE_DIRECTORY,
                    targetDirectories: [LuckyWheelReward::IMAGE_DIRECTORY.'/'],
                ),
                'image_storage_type' => $reward['image'] ? 'public' : null,
                'is_active' => true,
                'display_order' => $displayOrder + 1,
            ]);

            Translation::insert([
                'translationable_type' => LuckyWheelReward::class,
                'translationable_id' => $savedReward->id,
                'locale' => $arabicLocale,
                'key' => 'name',
                'value' => $reward['ar'],
            ]);
        }
    }

    private function createSettingIfMissing(string $type, string $value): void
    {
        if (!BusinessSetting::where('type', $type)->exists()) {
            BusinessSetting::insert(['type' => $type, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
