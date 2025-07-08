<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturasTable extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id(); // id SERIAL
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('ruc_cliente', 15);
            $table->text('razon_social');
            $table->timestamp('fecha_emision')->useCurrent();
            $table->decimal('total', 10, 2);
            $table->boolean('estado')->default(true);
            $table->timestamps(); // crea created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
}
