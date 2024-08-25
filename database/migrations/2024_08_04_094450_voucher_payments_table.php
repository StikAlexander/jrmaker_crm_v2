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
            $table->date('issue_date');  // Sin valor predeterminado
            $table->date('due_date')->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); 
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete(); 
            $table->date('payment_date');
            $table->integer('amount');
            $table->string('payment_support')->nullable();
            $table->enum('confirmation_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_payments');
    }
};
