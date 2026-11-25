<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The customer app opens with a burst of API calls on a single-core server, so payloads
 * it does not read must stay out of the responses.
 */
class CustomerApiPayloadTest extends TestCase
{
    public function test_categories_list_keeps_the_product_count_but_not_the_products(): void
    {
        $categoryController = file_get_contents(app_path('Http/Controllers/RestAPI/v1/CategoryController.php'));

        $this->assertStringContainsString("->withCount(['product' => function", $categoryController, 'The app shows product_count on each category.');
        $this->assertStringContainsString("\$categories->each(fn (Category \$category) => \$category->makeHidden('product'));", $categoryController);
        $this->assertLessThan(
            strpos($categoryController, "makeHidden('product')"),
            strpos($categoryController, 'CategoryManager::getPriorityWiseCategorySortQuery(query: $categories);'),
            'The "most ordered" sort reads the loaded products, so they are hidden only after sorting.'
        );
    }

    public function test_tables_read_on_every_app_launch_get_lookup_indexes_safely(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_11_26_003145_add_indexes_to_hot_storefront_tables.php'));

        foreach ([
            "'carts_customer_id_is_guest_index' => ['customer_id', 'is_guest']",
            "'cart_shippings_cart_group_id_index' => ['cart_group_id']",
            "'wishlists_customer_id_product_id_index' => ['customer_id', 'product_id']",
            "'shipping_addresses_customer_id_is_guest_index' => ['customer_id', 'is_guest']",
            "'translations_translationable_locale_index' => ['translationable_type', 'translationable_id', 'locale']",
        ] as $index) {
            $this->assertStringContainsString($index, $migration);
        }

        /* Re-running on a server where an index or column differs must not fail the deploy. */
        $this->assertStringContainsString('&& ! Schema::hasIndex($table, $indexName);', $migration);
        $this->assertStringContainsString('&& Schema::hasColumns($table, $columns)', $migration);
    }
}
