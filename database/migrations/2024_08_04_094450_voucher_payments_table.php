<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_support')->nullable();
            $table->enum('confirmation_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Establecer el valor predeterminado de issue_date después de crear la tabla
        DB::statement('ALTER TABLE voucher_payments ALTER COLUMN issue_date SET DEFAULT (CURRENT_DATE)');
    }

    public function down()
    {
        Schema::dropIfExists('voucher_payments');
    }
};