<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('image');
            $table->string('storage', 20)->default('public');
            $table->string('uploaded_by_type', 32);
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->string('uploaded_by_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_proofs');
    }
};
