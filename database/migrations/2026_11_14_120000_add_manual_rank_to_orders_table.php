<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('manual_rank')->nullable()->index()->comment('Place in the list arranged by staff, 1 = first; NULL = not arranged');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['manual_rank']);
            $table->dropColumn('manual_rank');
        });
    }
};
