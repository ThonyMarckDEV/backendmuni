<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idActivo');
            $table->foreign('idActivo')->references('idActivo')->on('activos')->onDelete('cascade');
            $table->unsignedBigInteger('idUsuario')->comment('Usuario con rol "usuario" (idRol = 2)');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('set null');
            $table->unsignedBigInteger('idTecnico')->nullable()->comment('Usuario con rol "tecnico" (idRol = 3)');
            $table->foreign('idTecnico')->references('idUsuario')->on('usuarios')->onDelete('set null');
            $table->tinyInteger('prioridad')->comment('0: Baja, 1: Media, 2: Alta');
            $table->string('titulo');
            $table->text('descripcion');
            $table->date('fecha_reporte');
            $table->tinyInteger('estado')->default(0)->comment('0: Pendiente, 1: En progreso, 2: Resuelto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos');
    }
};