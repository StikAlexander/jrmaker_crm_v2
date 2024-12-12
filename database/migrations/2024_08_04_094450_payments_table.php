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
            $table->unsignedBigInteger('payment_number')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('external_reference')->nullable();
            $table->integer('amount');
            $table->string('transaction_id')->nullable();
            $table->string('payment_link_id')->nullable();
            $table->string('payment_link')->nullable();
            $table->enum('payment_status', ['Pending', 'Completed', 'Failed', 'Declined', 'Error', 'Cancelled', 'Voided'])->default('Pending');
            $table->json('api_response')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
