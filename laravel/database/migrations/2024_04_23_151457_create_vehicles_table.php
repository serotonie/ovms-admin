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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->string('module_id', 32)->unique();
            $table->string('module_username', 32)->unique();
            $table->string('module_pwd');
            $table->boolean('mqtt_superuser')->default(false);
            $table->foreignId('owner_id')
                ->constrained(table: 'users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('main_user_id')
                ->nullable()
                ->constrained(table: 'users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestampTz('last_seen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
