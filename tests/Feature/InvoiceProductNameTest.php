<?php

namespace Tests\Feature;

use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Translation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceProductNameTest extends TestCase
{
    private const PRODUCT_ID = 51;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key')->nullable();
            $table->text('value')->nullable();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->text('product_details')->nullable();
            $table->timestamps();
        });
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('data_type');
            $table->unsignedBigInteger('data_id');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
        });
        config(['app.invoice_product_name_locale' => 'eg']);
    }

    public function test_invoice_shows_the_arabic_product_name(): void
    {
        $this->createProductNameTranslation(locale: 'en', name: 'Omani Lobster');
        $this->createProductNameTranslation(locale: 'eg', name: 'استاكوزا عمانية');

        $orderDetail = OrderDetail::with('invoiceProductNameTranslation')->find($this->createOrderDetail()->id);

        $this->assertSame('استاكوزا عمانية', $orderDetail->getInvoiceProductName());
    }

    public function test_invoice_falls_back_to_the_ordered_product_name_without_an_arabic_translation(): void
    {
        $this->createProductNameTranslation(locale: 'en', name: 'Lobster Translation');

        $orderDetail = $this->createOrderDetail()->setRelation('product', null);

        $this->assertSame('Omani Lobster', $orderDetail->getInvoiceProductName());
    }

    private function createOrderDetail(): OrderDetail
    {
        return OrderDetail::create([
            'order_id' => 1,
            'product_id' => self::PRODUCT_ID,
            'product_details' => json_encode(['id' => self::PRODUCT_ID, 'name' => 'Omani Lobster']),
        ]);
    }

    private function createProductNameTranslation(string $locale, string $name): void
    {
        Translation::create([
            'translationable_type' => Product::class,
            'translationable_id' => self::PRODUCT_ID,
            'locale' => $locale,
            'key' => 'name',
            'value' => $name,
        ]);
    }
}
