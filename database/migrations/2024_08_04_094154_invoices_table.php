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
            $table->decimal('total_amount', 10, 2);
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); 
            $table->enum('status', ['Pending', 'Paid', 'Cancelled', 'Under Review'])->default('Pending');
            $table->decimal('pending_amount', 10, 2)->default(0.00);
            $table->decimal('total_paid', 10, 2)->default(0.00);
            $table->string('invoice_pdf');
            $table->string('description')->nullable();
            $table->timestamps();
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
