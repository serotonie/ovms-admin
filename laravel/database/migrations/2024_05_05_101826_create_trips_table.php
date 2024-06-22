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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->datetimeTz('start_time');
            $table->datetimeTz('stop_time')->nullable();
            $table->float('start_point_lat');
            $table->float('start_point_long');
            $table->float('stop_point_lat')->nullable();
            $table->float('stop_point_long')->nullable();
            $table->float('distance', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
