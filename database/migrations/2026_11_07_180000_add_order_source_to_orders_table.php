<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_source', 30)->default('app')->index();
            $table->string('external_order_id', 64)->nullable();
            $table->unique(['order_source', 'external_order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_source', 'external_order_id']);
            $table->dropIndex(['order_source']);
            $table->dropColumn(['order_source', 'external_order_id']);
        });
    }
};
