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
       Schema::create('activos', function (Blueprint $table) {
            $table->bigIncrements('idActivo');
            $table->string('codigo_inventario')->unique();
            $table->string('ubicacion');
            $table->string('tipo');
            $table->string('marca_modelo');
            $table->boolean('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
