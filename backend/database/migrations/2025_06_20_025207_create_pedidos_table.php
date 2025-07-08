<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade'); // vendedor
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('total', 10, 2)->nullable();
            $table->enum('estado_pedido', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->boolean('estado')->default(true);
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
}
