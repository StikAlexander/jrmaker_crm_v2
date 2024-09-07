<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Crear la tabla voucher_payments desde cero
        Schema::create('voucher_payments', function (Blueprint $table) {
            $table->id();  // ID principal de la tabla
            $table->unsignedBigInteger('voucher_number')->unique();  // Número de voucher único
            $table->date('issue_date');  // Fecha de emisión del voucher
            $table->date('due_date')->nullable();  // Fecha de vencimiento, opcional
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');  // Relación con la tabla de usuarios (cliente)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();  // Usuario que creó el voucher
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();  // Usuario que confirmó el voucher
            $table->date('payment_date');  // Fecha de pago
            $table->integer('amount');  // Monto del voucher
            $table->enum('confirmation_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');  // Estado de confirmación del voucher

            // Nuevos campos para la integración con links de pago
            $table->string('payment_link')->nullable();  // Link generado por la API de pagos
            $table->enum('payment_status', ['Pending', 'Completed', 'Failed', 'Cancelled'])->default('Pending');  // Estado del pago
            $table->json('api_response')->nullable();  // Respuesta completa de la API de pago

            $table->timestamps();  // Timestamps (created_at y updated_at)
            $table->softDeletes();  // Soft delete
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_payments');  // Eliminar la tabla si se ejecuta el rollback
    }
};
