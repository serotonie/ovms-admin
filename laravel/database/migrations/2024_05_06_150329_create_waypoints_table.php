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
        Schema::create('waypoints', function (Blueprint $table) {
            $table->id();
            $table->datetimeTz('timestamp');
            $table->foreignId('trip_id')->constrained();
            $table->float('gpshdop', 1)->nullable();
            $table->integer('odometer', unsigned: true);
            $table->float('position_lat');
            $table->float('position_long');
            $table->float('distance', 3);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waypoints');
    }
};
