<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->boolean('estado')->default(true);
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
}
