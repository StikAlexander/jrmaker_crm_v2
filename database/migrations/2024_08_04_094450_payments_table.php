<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_number')->unique();  // Número único de pago
            $table->date('issue_date');  // Fecha de emisión del pago
            $table->date('due_date')->nullable();  // Fecha de vencimiento del pago
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');  // Cliente (usuario)
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();  // Confirmación (usuario)
            $table->string('external_reference')->nullable();
            $table->date('payment_date')->nullable();  // Fecha del pago realizado
            $table->integer('amount');  // Monto total del pago
            $table->string('transaction_id')->nullable();  // ID de la transacción con Wompi
            $table->string('payment_link_id')->nullable();  // ID del link de pago generado por Wompi
            $table->string('payment_link')->nullable();  // Link de pago generado por Wompi
            $table->enum('payment_status', ['Pending', 'Completed', 'Failed', 'Declined', 'Error', 'Cancelled'])->default('Pending');  // Estado del pago
            $table->string('status_message')->nullable();  // Mensaje de estado devuelto por Wompi
            $table->string('return_code')->nullable();  // Código de retorno del banco/método de pago
            $table->json('api_response')->nullable();  // Respuesta completa de la API de Wompi (JSON)
            $table->string('payment_method_type')->nullable();  // Tipo de método de pago (PSE, Tarjeta, etc.)
            $table->string('reference')->nullable();  // Referencia interna o de Wompi
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
