<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_wheel_won_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreignId('spin_id')->unique()->constrained('lucky_wheel_spins');
            $table->foreignId('reward_id')->nullable()->constrained('lucky_wheel_rewards')->nullOnDelete();
            $table->string('name');
            $table->json('name_translations')->nullable();
            $table->string('image')->nullable();
            $table->string('image_storage_type', 10)->nullable();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->index(['customer_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_won_gifts');
    }
};
