<?php

namespace Tests\Feature;

use App\Services\OrderService;
use Tests\TestCase;

class OrderServiceEasyOrdersProductDetailsTest extends TestCase
{
    public function test_easy_orders_text_product_id_is_moved_to_external_id(): void
    {
        $details = (new OrderService())->getCustomerAppProductDetails(productDetails: ['source' => 'easy_orders', 'id' => 'a1b2-c3', 'name' => 'Salmon']);

        $this->assertNull($details['id']);
        $this->assertSame('a1b2-c3', $details['external_id']);
        $this->assertSame('Salmon', $details['name']);
    }

    public function test_app_product_details_stay_unchanged(): void
    {
        $productDetails = ['id' => 15, 'name' => 'Shrimp'];

        $this->assertSame($productDetails, (new OrderService())->getCustomerAppProductDetails(productDetails: $productDetails));
    }
}
