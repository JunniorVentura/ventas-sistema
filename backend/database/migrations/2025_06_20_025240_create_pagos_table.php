<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosTable extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->enum('metodo_pago', ['efectivo', 'yape', 'transferencia']);
            $table->enum('estado_pago', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->timestamp('fecha_pago')->useCurrent();
            $table->boolean('estado')->default(true);
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
}
