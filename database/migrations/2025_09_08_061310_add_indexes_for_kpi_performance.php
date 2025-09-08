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
        Schema::table('trips', function (Blueprint $table) {
            $table->index(['status', 'start_time', 'end_time'], 'trips_status_time_index');
            $table->index(['company_id', 'status'], 'trips_company_status_index');
            $table->index(['driver_id', 'start_time', 'end_time'], 'trips_driver_time_index');
            $table->index(['vehicle_id', 'start_time', 'end_time'], 'trips_vehicle_time_index');
            $table->index('start_time', 'trips_start_time_index');
            $table->index('end_time', 'trips_end_time_index');
        });

        Schema::table('driver_vehicle', function (Blueprint $table) {
            $table->index(['driver_id', 'vehicle_id'], 'driver_vehicle_composite_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex('trips_status_time_index');
            $table->dropIndex('trips_company_status_index');
            $table->dropIndex('trips_driver_time_index');
            $table->dropIndex('trips_vehicle_time_index');
            $table->dropIndex('trips_start_time_index');
            $table->dropIndex('trips_end_time_index');
        });

        Schema::table('driver_vehicle', function (Blueprint $table) {
            $table->dropIndex('driver_vehicle_composite_index');
        });
    }
};
