<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // Agrega los campos que pueden ser llenados masivamente
    protected $fillable = ['reporte_tipo', 'fecha_inicio', 'fecha_fin', 'name', 'description', 'generated_at'];
}
