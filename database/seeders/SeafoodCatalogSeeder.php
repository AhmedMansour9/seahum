<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSeo;
use App\Models\Tag;
use App\Models\Translation;
use Database\Seeders\Concerns\PublishesSeedImages;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeds a bilingual (English / Arabic) fish and seafood demo catalogue for the
 * in-house shop.
 *
 * The seeder is idempotent: every product is matched on its SKU
 * (`products.code`), translations and SEO records are updated in place and
 * seed images are only copied when they are missing. Nothing is truncated,
 * deleted or randomised, so it is safe to re-run on a populated database.
 *
 * Run it on its own with:
 *   php artisan db:seed --class="Database\Seeders\SeafoodCatalogSeeder"
 */
class SeafoodCatalogSeeder extends Seeder
{
    use PublishesSeedImages;

    private const SEED_IMAGE_DIRECTORY = 'assets/front-end/img/seafood/products';

    private const IMAGE_TARGET_DIRECTORIES = ['product/', 'product/thumbnail/', 'product/meta/'];

    /**
     * @var array<string, array<string, string>>
     */
    private const SPEC_LABELS = [
        'en' => [
            'heading' => 'Product specifications',
            'species' => 'Species',
            'scientific' => 'Scientific name',
            'origin' => 'Origin',
            'source' => 'Source',
            'condition' => 'Condition',
            'preparation' => 'Preparation',
            'packaging' => 'Packaging',
            'unit' => 'Sold by',
            'weight' => 'Weight',
            'storage' => 'Storage',
            'cooking' => 'Cooking suggestions',
            'nutrition' => 'Nutritional highlights',
        ],
        'ar' => [
            'heading' => 'مواصفات المنتج',
            'species' => 'النوع',
            'scientific' => 'الاسم العلمي',
            'origin' => 'المصدر الجغرافي',
            'source' => 'طريقة الإنتاج',
            'condition' => 'الحالة',
            'preparation' => 'طريقة التجهيز',
            'packaging' => 'التعبئة',
            'unit' => 'وحدة البيع',
            'weight' => 'الوزن',
            'storage' => 'طريقة الحفظ',
            'cooking' => 'اقتراحات الطهي',
            'nutrition' => 'أبرز القيم الغذائية',
        ],
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private const UNIT_LABELS = [
        'kg' => ['en' => 'kilogram (kg)', 'ar' => 'الكيلوجرام (كجم)'],
        'pc' => ['en' => 'piece', 'ar' => 'الحبة'],
    ];

    public function run(): void
    {
        $this->call(SeafoodCatalogSupportSeeder::class);

        $catalog = require database_path('seeders/data/seafood-catalog.php');
        $arabicLocale = self::resolveArabicLocale();
        $adminId = Admin::orderBy('id')->value('id');
        $shopId = getInHouseShopConfig('id');

        if (! $adminId) {
            throw new RuntimeException('No admin account found: run the admin seeders or import the database backup first.');
        }

        $categoryIds = Category::whereIn('slug', $this->getCatalogCategorySlugs($catalog))->pluck('id', 'slug');
        $attributeIds = Attribute::whereIn('name', $catalog['attributes'])->pluck('id', 'name');

        foreach ($catalog['products'] as $item) {
            $this->seedProduct(
                item: $item,
                categoryIds: $categoryIds->toArray(),
                attributeIds: $attributeIds->toArray(),
                adminId: $adminId,
                shopId: $shopId,
                arabicLocale: $arabicLocale,
            );
        }

        cacheRemoveByType(type: 'products');
        cacheRemoveByType(type: 'categories');

        $this->command?->info('Seeded '.count($catalog['products']).' seafood products.');
    }

    /**
     * Resolves the Arabic locale that is actually installed in the shop,
     * falling back to the project default ("eg") when none is configured.
     */
    public static function resolveArabicLocale(): string
    {
        $languages = getWebConfig(name: 'language');

        foreach (is_array($languages) ? $languages : [] as $language) {
            if (in_array($language['code'] ?? null, ['eg', 'sa', 'ar'], true)) {
                return $language['code'];
            }
        }

        return 'eg';
    }

    private function seedProduct(
        array $item,
        array $categoryIds,
        array $attributeIds,
        int $adminId,
        int|string|null $shopId,
        string $arabicLocale,
    ): void {
        $categoryId = $categoryIds[$item['category']] ?? null;
        $subCategoryId = $categoryIds[$item['sub_category']] ?? null;

        if (! $categoryId || ! $subCategoryId) {
            throw new RuntimeException('Missing seafood category for SKU '.$item['sku'].'.');
        }

        $variation = $this->buildVariation(item: $item, attributeIds: $attributeIds);
        $image = $this->publishSeedImage(
            fileName: $item['image'],
            sourceDirectory: self::SEED_IMAGE_DIRECTORY,
            targetDirectories: self::IMAGE_TARGET_DIRECTORIES,
        );
        $discount = $this->getDiscount(item: $item);

        $product = Product::withoutGlobalScopes()->updateOrCreate(
            ['code' => $item['sku']],
            [
                'added_by' => 'admin',
                'user_id' => $adminId,
                'shop_id' => $shopId,
                'name' => $item['name']['en'],
                'slug' => $item['slug'],
                'product_type' => 'physical',
                'category_ids' => json_encode([
                    ['id' => (string) $categoryId, 'position' => 1],
                    ['id' => (string) $subCategoryId, 'position' => 2],
                ]),
                'category_id' => $categoryId,
                'sub_category_id' => $subCategoryId,
                'sub_sub_category_id' => null,
                'brand_id' => null,
                'unit' => $item['unit'],
                'details' => $this->buildDetails(item: $item, language: 'en'),
                'colors' => json_encode([]),
                'choice_options' => json_encode($variation['choice_options']),
                'attributes' => json_encode($variation['attributes']),
                'variation' => json_encode($variation['variation']),
                'unit_price' => $item['price'],
                'purchase_price' => 0,
                'tax' => 0,
                'tax_type' => 'percent',
                'tax_model' => 'exclude',
                'discount' => $discount['amount'],
                'discount_type' => $discount['type'],
                'current_stock' => $variation['stock'],
                'minimum_order_qty' => 1,
                'video_provider' => 'youtube',
                'video_url' => null,
                'status' => 1,
                'request_status' => 1,
                'featured_status' => 1,
                'shipping_cost' => 0,
                'multiply_qty' => 0,
                'color_image' => json_encode([]),
                'images' => json_encode($image ? [['image_name' => $image, 'storage' => 'public']] : []),
                'thumbnail' => $image,
                'thumbnail_storage_type' => $image ? 'public' : null,
                'meta_title' => $item['seo']['title'],
                'meta_description' => $item['seo']['description'],
                'meta_image' => $image,
            ],
        );

        $this->syncTranslations(product: $product, item: $item, arabicLocale: $arabicLocale);
        $this->syncTags(product: $product, tags: $item['tags']);
        $this->syncSeo(product: $product, item: $item, image: $image);
    }

    /**
     * Builds the choice options, attribute ids and variation rows in the same
     * shape the admin product form produces, and returns the stock that
     * matches them.
     *
     * @return array{choice_options: array, attributes: array, variation: array, stock: int}
     */
    private function buildVariation(array $item, array $attributeIds): array
    {
        if (empty($item['variants'])) {
            return ['choice_options' => [], 'attributes' => [], 'variation' => [], 'stock' => $item['stock']];
        }

        $choiceOptions = [];
        $selectedAttributes = [];
        $optionSets = [];

        foreach ($item['variants']['axes'] as $attributeName => $options) {
            $attributeId = $attributeIds[$attributeName] ?? null;

            if (! $attributeId) {
                throw new RuntimeException('Missing attribute "'.$attributeName.'" for SKU '.$item['sku'].'.');
            }

            $selectedAttributes[] = $attributeId;
            $choiceOptions[] = [
                'name' => 'choice_'.$attributeId,
                'title' => $attributeName,
                'options' => $options,
            ];
            $optionSets[] = $options;
        }

        $variation = [];
        $stock = 0;

        foreach ($this->getCombinations(optionSets: $optionSets) as $combination) {
            $type = implode('-', array_map(static fn (string $option): string => str_replace(' ', '', $option), $combination));
            $definition = $item['variants']['combinations'][$type] ?? null;

            if (! $definition) {
                throw new RuntimeException('Missing variation "'.$type.'" for SKU '.$item['sku'].'.');
            }

            $variation[] = [
                'type' => $type,
                'price' => $definition['price'],
                'sku' => $definition['sku'],
                'qty' => $definition['qty'],
            ];
            $stock += $definition['qty'];
        }

        return [
            'choice_options' => $choiceOptions,
            'attributes' => $selectedAttributes,
            'variation' => $variation,
            'stock' => $stock,
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $optionSets
     * @return array<int, array<int, string>>
     */
    private function getCombinations(array $optionSets): array
    {
        $combinations = [[]];

        foreach ($optionSets as $options) {
            $expanded = [];

            foreach ($combinations as $combination) {
                foreach ($options as $option) {
                    $expanded[] = array_merge($combination, [$option]);
                }
            }

            $combinations = $expanded;
        }

        return $combinations;
    }

    /**
     * The schema has no compare-at price: a higher "was" price is modelled as
     * a flat discount, exactly as the admin panel does.
     *
     * @return array{amount: float, type: string}
     */
    private function getDiscount(array $item): array
    {
        $compareAt = $item['compare_at'] ?? null;

        if (! $compareAt || $compareAt <= $item['price']) {
            return ['amount' => 0, 'type' => 'flat'];
        }

        return ['amount' => round($compareAt - $item['price'], 2), 'type' => 'flat'];
    }

    /**
     * Arabic name and description are stored as polymorphic translation rows.
     * The order matters: the Product model reads translations[0] as the name
     * and translations[1] as the details.
     */
    private function syncTranslations(Product $product, array $item, string $arabicLocale): void
    {
        $values = [
            'name' => $item['name']['ar'],
            'description' => $this->buildDetails(item: $item, language: 'ar'),
        ];

        foreach ($values as $key => $value) {
            Translation::updateOrInsert(
                [
                    'translationable_type' => Product::class,
                    'translationable_id' => $product->id,
                    'locale' => $arabicLocale,
                    'key' => $key,
                ],
                ['value' => $value],
            );
        }
    }

    private function syncTags(Product $product, array $tags): void
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            $tagIds[] = Tag::firstOrCreate(['tag' => trim($tag)])->id;
        }

        $product->tags()->sync($tagIds);
    }

    private function syncSeo(Product $product, array $item, ?string $image): void
    {
        ProductSeo::updateOrCreate(
            ['product_id' => $product->id],
            [
                'title' => $item['seo']['title'],
                'description' => $item['seo']['description'],
                'index' => '',
                'no_follow' => '',
                'no_image_index' => '',
                'no_archive' => '',
                'no_snippet' => 0,
                'max_snippet' => 0,
                'max_snippet_value' => 0,
                'max_video_preview' => 0,
                'max_video_preview_value' => 0,
                'max_image_preview' => 0,
                'max_image_preview_value' => 0,
                'image' => $image,
            ],
        );
    }

    /**
     * Renders the customer-facing description: an introduction, the full
     * specification table, storage instructions, cooking suggestions and
     * nutritional highlights.
     */
    private function buildDetails(array $item, string $language): string
    {
        $labels = self::SPEC_LABELS[$language];
        $specification = $item['spec'];

        $rows = [
            $labels['species'] => e($specification['species'][$language]),
            $labels['scientific'] => '<em>'.e($specification['scientific']).'</em>',
            $labels['origin'] => e($specification['origin'][$language]),
            $labels['source'] => e($specification['source'][$language]),
            $labels['condition'] => e($specification['condition'][$language]),
            $labels['preparation'] => e($specification['cut'][$language]),
            $labels['packaging'] => e($specification['packaging'][$language]),
            $labels['unit'] => e(self::UNIT_LABELS[$item['unit']][$language] ?? $item['unit']),
            $labels['weight'] => e($specification['weight'][$language]),
        ];

        $specificationRows = '';
        foreach ($rows as $label => $value) {
            $specificationRows .= '<tr><th scope="row">'.e($label).'</th><td>'.$value.'</td></tr>';
        }

        $cookingItems = '';
        foreach ($item['cooking'][$language] as $suggestion) {
            $cookingItems .= '<li>'.e($suggestion).'</li>';
        }

        $direction = $language === 'ar' ? ' dir="rtl"' : '';

        return '<div'.$direction.'>'
            .'<p><strong>'.e($item['short'][$language]).'</strong></p>'
            .'<p>'.e($item['intro'][$language]).'</p>'
            .'<h4>'.e($labels['heading']).'</h4>'
            .'<table class="table"><tbody>'.$specificationRows.'</tbody></table>'
            .'<h4>'.e($labels['storage']).'</h4>'
            .'<p>'.e($item['storage'][$language]).'</p>'
            .'<h4>'.e($labels['cooking']).'</h4>'
            .'<ul>'.$cookingItems.'</ul>'
            .'<h4>'.e($labels['nutrition']).'</h4>'
            .'<p>'.e($item['nutrition'][$language]).'</p>'
            .'</div>';
    }

    /**
     * @return array<int, string>
     */
    private function getCatalogCategorySlugs(array $catalog): array
    {
        $slugs = [];

        foreach ($catalog['categories'] as $category) {
            $slugs[] = $category['slug'];

            foreach ($category['children'] as $child) {
                $slugs[] = $child['slug'];
            }
        }

        return $slugs;
    }
}
