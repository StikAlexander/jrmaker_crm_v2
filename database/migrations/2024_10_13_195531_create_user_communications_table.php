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
        Schema::create('user_communications', function (Blueprint $table) {
            $table->id();
            $table->string('template_id');  // Campo para guardar la plantilla seleccionada
            $table->string('title')->nullable();  // Campo para el título del correo
            $table->text('message')->nullable();  // Campo para el mensaje del correo
            $table->timestamps();  // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_communications');
    }
};
