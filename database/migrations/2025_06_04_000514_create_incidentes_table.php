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
        Schema::create('incidentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained()->onDelete('cascade');
            $table->text('descripcion');
            $table->date('fecha_reporte');
            $table->boolean('estado')->default(0)->comment('0: Pendiente , 1:En progreso ,2:Resuelto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
