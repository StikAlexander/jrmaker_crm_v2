<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voucher_payment_invoice', function (Blueprint $table) {
            $table->foreignId('voucher_payment_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); 
            $table->decimal('amount', 10, 2);
            $table->primary(['voucher_payment_id', 'invoice_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_payment_invoice');
    }
};