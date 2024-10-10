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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 10)->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->integer('total_amount');
            $table->integer('pending_amount')->default(0);
            $table->integer('total_paid')->default(0);
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            
            $table->enum('status', ['Pending', 'Paid', 'Cancelled'])->default('Pending');
            $table->string('invoice_pdf')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
