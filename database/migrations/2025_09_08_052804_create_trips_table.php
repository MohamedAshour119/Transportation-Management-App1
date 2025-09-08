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
            $table->foreignId('company_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('driver_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'scheduled'])->default('pending');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('start_location');
            $table->string('end_location');
            $table->decimal('distance', 8, 2)->nullable();
            $table->decimal('fuel_consumption', 8, 2)->nullable();
            $table->decimal('fuel_price', 8, 2)->nullable();
            $table->decimal('fuel_cost', 8, 2)->nullable();
            $table->decimal('insurance_cost', 8, 2)->nullable();
            $table->decimal('maintenance_cost', 8, 2)->nullable();
            $table->decimal('total_cost', 8, 2)->nullable();            
            $table->timestamps();
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
