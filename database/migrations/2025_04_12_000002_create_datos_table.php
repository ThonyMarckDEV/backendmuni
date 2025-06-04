<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos', function (Blueprint $table) {
            $table->bigIncrements('idDatos');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->string('dni')->nullable();
            $table->string('telefono')->nullable();
            $table->string('especializacion')->nullable();
            $table->string('area')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos');
    }
};