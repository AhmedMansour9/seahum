<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Translation;
use Database\Seeders\Concerns\PublishesSeedImages;
use Illuminate\Database\Seeder;

/**
 * Creates the minimum reference data the seafood catalogue depends on:
 * its categories (with Arabic names and icons) and the variation attributes
 * used by the product options. Existing records with the same slug or name
 * are reused as-is, and an icon is only written when the category does not
 * already have one.
 */
class SeafoodCatalogSupportSeeder extends Seeder
{
    use PublishesSeedImages;

    private const SEED_IMAGE_DIRECTORY = 'assets/front-end/img/seafood/categories';

    private const IMAGE_TARGET_DIRECTORIES = ['category/'];

    public function run(): void
    {
        $catalog = require database_path('seeders/data/seafood-catalog.php');
        $arabicLocale = SeafoodCatalogSeeder::resolveArabicLocale();

        foreach ($catalog['categories'] as $category) {
            $parent = $this->createCategory(
                category: $category,
                parentId: 0,
                position: 0,
                priority: $category['priority'],
                homeStatus: 1,
                arabicLocale: $arabicLocale,
            );

            foreach ($category['children'] as $child) {
                $this->createCategory(
                    category: $child,
                    parentId: $parent->id,
                    position: 1,
                    priority: 0,
                    homeStatus: 0,
                    arabicLocale: $arabicLocale,
                );
            }
        }

        foreach ($catalog['attributes'] as $attribute) {
            Attribute::firstOrCreate(['name' => $attribute]);
        }

        cacheRemoveByType(type: 'categories');
    }

    private function createCategory(
        array $category,
        int $parentId,
        int $position,
        int $priority,
        int $homeStatus,
        string $arabicLocale,
    ): Category {
        $icon = $this->publishSeedImage(
            fileName: $category['image'] ?? null,
            sourceDirectory: self::SEED_IMAGE_DIRECTORY,
            targetDirectories: self::IMAGE_TARGET_DIRECTORIES,
        );

        $model = Category::firstOrCreate(
            ['slug' => $category['slug']],
            [
                'name' => $category['name']['en'],
                'parent_id' => $parentId,
                'position' => $position,
                'priority' => $priority,
                'home_status' => $homeStatus,
                'icon' => $icon,
                'icon_storage_type' => $icon ? 'public' : null,
            ],
        );

        if ($icon && ! $model->icon) {
            $model->update(['icon' => $icon, 'icon_storage_type' => 'public']);
        }

        Translation::updateOrInsert(
            [
                'translationable_type' => Category::class,
                'translationable_id' => $model->id,
                'locale' => $arabicLocale,
                'key' => 'name',
            ],
            ['value' => $category['name']['ar']],
        );

        return $model;
    }
}
