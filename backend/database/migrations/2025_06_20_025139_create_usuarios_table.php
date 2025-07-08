<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->string('nombre', 100)->nullable();
            $table->string('email', 100)->unique();
            $table->text('password');
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            $table->boolean('estado')->default(true);
            $table->timestamp('token_expiration')->nullable(); //se añadió para dar un tiempo determinado al token
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
}
