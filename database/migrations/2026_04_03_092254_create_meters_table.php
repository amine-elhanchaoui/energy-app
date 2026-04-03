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
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('quartier_id')->constrained('quartiers', 'id')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['electricity', 'gas', 'water']);
            $table->string('location')->nullable();//define location of the meter in the house (e.g., "kitchen", "living room")
            $table->enum('unit', ['kWh', 'm³', 'liters']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meters');
    }
};