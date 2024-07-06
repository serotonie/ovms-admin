<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('trips', 'category_id')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->default(1)->after('vehicle_id');
                $table->foreign('category_id')->references('id')->on('categories');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }
};
