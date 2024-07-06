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
            $table->float('start_point_lat');
            $table->float('start_point_long');
            $table->string('start_house_number')->nullable();
            $table->string('start_road')->nullable();
            $table->string('start_village')->nullable();
            $table->string('start_postcode')->nullable();
            $table->string('start_country')->nullable();
            $table->datetimeTz('stop_time')->nullable();
            $table->float('stop_point_lat')->nullable();
            $table->float('stop_point_long')->nullable();
            $table->string('stop_house_number')->nullable();
            $table->string('stop_road')->nullable();
            $table->string('stop_village')->nullable();
            $table->string('stop_postcode')->nullable();
            $table->string('stop_country')->nullable();
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
