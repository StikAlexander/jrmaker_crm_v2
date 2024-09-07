<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id(); // ID del intento de pago
            $table->foreignId('voucher_payment_id')->constrained('voucher_payments')->onDelete('cascade'); // Relación con 'voucher_payments'
            $table->enum('status', ['Pending', 'Completed', 'Failed'])->default('Pending'); // Estado del intento
            $table->json('response')->nullable(); // Respuesta de la API o detalles del intento
            $table->timestamps(); // Fechas de creación y actualización
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_attempts');
    }
};
