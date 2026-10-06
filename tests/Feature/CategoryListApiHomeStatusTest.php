<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategoryListApiHomeStatusTest extends TestCase
{
    private const CATEGORIES_ENDPOINT = '/api/v1/categories?guest_id=1';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect());
        $this->createCategoryListingTables();
    }

    public function test_main_categories_with_home_status_off_are_not_listed(): void
    {
        $visibleCategory = $this->createCategory(name: 'Fish', position: 0, homeStatus: 1);
        $this->createCategory(name: 'Hidden', position: 0, homeStatus: 0);

        $response = $this->getJson(self::CATEGORIES_ENDPOINT);

        $response->assertOk();
        $this->assertSame([$visibleCategory->id], array_column($response->json(), 'id'));
    }

    public function test_sub_and_sub_sub_categories_with_home_status_off_are_not_listed(): void
    {
        $mainCategory = $this->createCategory(name: 'Fish', position: 0, homeStatus: 1);
        $visibleSubCategory = $this->createCategory(name: 'Fresh', position: 1, homeStatus: 1, parentId: $mainCategory->id);
        $this->createCategory(name: 'Hidden sub', position: 1, homeStatus: 0, parentId: $mainCategory->id);
        $visibleSubSubCategory = $this->createCategory(name: 'Salmon', position: 2, homeStatus: 1, parentId: $visibleSubCategory->id);
        $this->createCategory(name: 'Hidden sub sub', position: 2, homeStatus: 0, parentId: $visibleSubCategory->id);

        $subCategories = $this->getJson(self::CATEGORIES_ENDPOINT)->assertOk()->json('0.childes');

        $this->assertSame([$visibleSubCategory->id], array_column($subCategories, 'id'));
        $this->assertSame([$visibleSubSubCategory->id], array_column($subCategories[0]['childes'], 'id'));
    }

    private function createCategory(string $name, int $position, int $homeStatus, int $parentId = 0): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'icon' => 'def.png',
            'parent_id' => $parentId,
            'position' => $position,
            'home_status' => $homeStatus,
        ]);
    }

    private function createCategoryListingTables(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable();
            $table->string('icon_storage_type')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->integer('position')->default(0);
            $table->boolean('home_status')->default(0);
            $table->boolean('auction_home_status')->default(0);
            $table->integer('priority')->nullable();
            $table->timestamps();
        });
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key')->nullable();
            $table->text('value')->nullable();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('added_by')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('sub_sub_category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('product_type')->default('physical');
            $table->boolean('status')->default(1);
            $table->boolean('request_status')->default(1);
            $table->timestamps();
        });
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('approved');
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
        });
    }
}
