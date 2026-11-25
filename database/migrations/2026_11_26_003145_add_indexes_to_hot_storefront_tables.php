<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The app and storefront read these tables on every launch or page view (cart, chosen shipping,
 * wishlist, saved addresses, reviews, order history, translations), but most had no index beyond
 * the primary key, so each lookup scanned the whole table.
 */
return new class extends Migration
{
    /**
     * @var array<string, array<string, array<int, string>>>
     */
    private array $indexes = [
        'carts' => [
            'carts_customer_id_is_guest_index' => ['customer_id', 'is_guest'],
            'carts_cart_group_id_index' => ['cart_group_id'],
            'carts_product_id_index' => ['product_id'],
        ],
        'cart_shippings' => [
            'cart_shippings_cart_group_id_index' => ['cart_group_id'],
        ],
        'wishlists' => [
            'wishlists_customer_id_product_id_index' => ['customer_id', 'product_id'],
        ],
        'reviews' => [
            'reviews_product_id_status_index' => ['product_id', 'status'],
            'reviews_customer_id_index' => ['customer_id'],
            'reviews_order_id_index' => ['order_id'],
        ],
        'shipping_addresses' => [
            'shipping_addresses_customer_id_is_guest_index' => ['customer_id', 'is_guest'],
        ],
        'order_status_histories' => [
            'order_status_histories_order_id_index' => ['order_id'],
        ],
        'deliveryman_notifications' => [
            'deliveryman_notifications_delivery_man_id_index' => ['delivery_man_id'],
        ],
        'translations' => [
            'translations_translationable_locale_index' => ['translationable_type', 'translationable_id', 'locale'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName => $columns) {
                if (! $this->canAddIndex(table: $table, indexName: $indexName, columns: $columns)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($indexName, $columns) {
                    $blueprint->index($columns, $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $tableIndexes) {
            foreach (array_keys($tableIndexes) as $indexName) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $indexName)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                        $blueprint->dropIndex($indexName);
                    });
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function canAddIndex(string $table, string $indexName, array $columns): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumns($table, $columns)
            && ! Schema::hasIndex($table, $indexName);
    }
};
