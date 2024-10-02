<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental
            $table->string('reporte_tipo'); // Tipo de reporte (facturas vencidas, pagos exitosos, etc.)
            $table->date('fecha_inicio');   // Fecha de inicio del reporte
            $table->date('fecha_fin');      // Fecha de fin del reporte
            $table->string('name')->nullable(); // Nombre del reporte (opcional)
            $table->text('description')->nullable(); // Descripción del reporte (opcional)
            $table->timestamp('generated_at')->nullable(); // Fecha de generación del reporte (opcional)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
