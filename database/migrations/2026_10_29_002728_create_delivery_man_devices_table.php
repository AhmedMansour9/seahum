<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->cascadeOnDelete();
            $table->string('auth_token', 100)->unique();
            $table->string('fcm_token')->nullable()->index();
            $table->timestamps();
        });

        DB::table('delivery_men')
            ->whereNotNull('auth_token')
            ->where('auth_token', '!=', '')
            ->orderBy('id')
            ->each(function (object $deliveryMan) {
                DB::table('delivery_man_devices')->insert([
                    'delivery_man_id' => $deliveryMan->id,
                    'auth_token' => $deliveryMan->auth_token,
                    'fcm_token' => $deliveryMan->fcm_token ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_devices');
    }
};
