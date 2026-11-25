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
}
