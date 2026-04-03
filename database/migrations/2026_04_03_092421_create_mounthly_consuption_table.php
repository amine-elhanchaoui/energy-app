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
        Schema::create('monthly_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_id')->constrained('meters', 'id')->onDelete('cascade');
            $table->date('month');
            $table->decimal('consumption_value', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_consumptions');
    }
};