<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_wheel_spins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreignId('reward_id')->nullable()->constrained('lucky_wheel_rewards')->nullOnDelete();
            $table->string('outcome_type', 20);
            $table->date('spin_date');
            $table->string('spin_frequency', 10);
            $table->json('reward_snapshot');
            $table->timestamps();

            $table->unique(['customer_id', 'spin_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_spins');
    }
};
