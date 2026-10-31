<?php

namespace Tests\Feature;

use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Translation;
use Tests\TestCase;

class OrderDetailLocalizedProductNameTest extends TestCase
{
    public function test_shows_the_arabic_product_name_when_the_panel_language_is_arabic(): void
    {
        session(['local' => 'eg']);

        $this->assertSame('استاكوزا عمانية', $this->makeOrderDetailWithProduct()->getLocalizedProductName());
    }

    public function test_shows_the_english_product_name_when_the_panel_language_is_english(): void
    {
        session(['local' => 'en']);

        $this->assertSame('Omani Lobster EN', $this->makeOrderDetailWithProduct()->getLocalizedProductName());
    }

    public function test_falls_back_to_the_base_name_without_a_translation_for_the_panel_language(): void
    {
        session(['local' => 'fr']);

        $this->assertSame('Omani Lobster', $this->makeOrderDetailWithProduct()->getLocalizedProductName());
    }

    public function test_falls_back_to_the_ordered_product_name_when_the_product_was_deleted(): void
    {
        session(['local' => 'eg']);

        $orderDetail = (new OrderDetail(['product_details' => json_encode(['name' => 'Deleted Lobster'])]))
            ->setRelation('productAllStatus', null);

        $this->assertSame('Deleted Lobster', $orderDetail->getLocalizedProductName());
    }

    private function makeOrderDetailWithProduct(): OrderDetail
    {
        $product = (new Product())->setRawAttributes(['name' => 'Omani Lobster'], sync: true)->setRelation('translations', collect([
            new Translation(['locale' => 'en', 'key' => 'name', 'value' => 'Omani Lobster EN']),
            new Translation(['locale' => 'eg', 'key' => 'description', 'value' => 'وصف']),
            new Translation(['locale' => 'eg', 'key' => 'name', 'value' => 'استاكوزا عمانية']),
        ]));

        return (new OrderDetail(['product_details' => json_encode(['name' => 'Ordered Lobster'])]))
            ->setRelation('productAllStatus', $product);
    }
}
