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
        Schema::create('activos_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idActivo');
            $table->unsignedBigInteger('idArea');
            $table->timestamps();

            // Foreign keys
            $table->foreign('idActivo')->references('idActivo')->on('activos')->onDelete('cascade');
            $table->foreign('idArea')->references('idArea')->on('areas')->onDelete('cascade');

            // Unique constraint to prevent duplicate assignments of the same activo to the same area
            $table->unique(['idActivo', 'idArea']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos_areas');
    }
};
