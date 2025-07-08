<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('tabla_afectada', 50);
            $table->integer('id_registro');
            $table->enum('accion', ['crear', 'editar', 'eliminar', 'login', 'logout']);
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
}
