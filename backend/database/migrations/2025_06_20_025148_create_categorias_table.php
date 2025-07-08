<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriasTable extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
}
