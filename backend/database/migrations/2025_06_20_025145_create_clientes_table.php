<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->string('nombre', 100)->nullable();
            $table->string('dni', 15)->nullable();
            $table->string('ruc', 15)->nullable();
            $table->string('razon_social', 150)->nullable(); // para facturas
            $table->text('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
}
