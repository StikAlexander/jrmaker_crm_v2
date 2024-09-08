<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('voucher_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_number')->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date')->default(now());
            $table->integer('amount');
            $table->enum('confirmation_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->string('external_reference')->nullable(); 
            $table->string('payment_link')->nullable();
            $table->enum('payment_status', ['Pending', 'Completed', 'Failed', 'Cancelled'])->default('Pending');
            $table->json('api_response')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_payments');
    }
};
