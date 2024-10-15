<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Obtener solo los usuarios con el rol de cliente y cargar su tipo de documento y el creador
        return User::role('client')->with(['documentType', 'createdBy'])->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Teléfono',
            'Número de documento',
            'Tipo de Documento',  // Nuevo campo
            'Estado',
            'Fecha de Creación',  // Nuevo campo
            'Última Actualización',  // Nuevo campo
            'Creado por',  // Nuevo campo
        ];
    }

    public function map($client): array
    {
        return [
            $client->name,
            $client->email,
            $client->phone,
            $client->document_number,
            $client->documentType->name ?? '-',  // Nombre del tipo de documento
            $client->status,
            $client->created_at->format('Y-m-d'),  // Fecha de creación
            $client->updated_at->format('Y-m-d'),  // Fecha de última actualización
            $client->createdBy->name ?? '-',  // Nombre del creador (si existe)
        ];
    }
}
