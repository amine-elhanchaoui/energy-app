<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Keep only the most recent reading for each (meter_id, date).
        DB::statement("
            DELETE r1 FROM readings r1
            INNER JOIN readings r2
                ON r1.meter_id = r2.meter_id
                AND r1.date = r2.date
                AND r1.id < r2.id
        ");

        // Keep only one row for each (meter_id, month).
        DB::statement("
            DELETE mc1 FROM monthly_consumptions mc1
            INNER JOIN monthly_consumptions mc2
                ON mc1.meter_id = mc2.meter_id
                AND mc1.month = mc2.month
                AND mc1.id < mc2.id
        ");

        Schema::table('readings', function (Blueprint $table) {
            $table->unique(['meter_id', 'date'], 'readings_meter_id_date_unique');
        });

        Schema::table('monthly_consumptions', function (Blueprint $table) {
            $table->unique(['meter_id', 'month'], 'monthly_consumptions_meter_id_month_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->dropUnique('readings_meter_id_date_unique');
        });

        Schema::table('monthly_consumptions', function (Blueprint $table) {
            $table->dropUnique('monthly_consumptions_meter_id_month_unique');
        });
    }
};
