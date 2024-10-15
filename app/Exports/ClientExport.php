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
        return User::role('client')->with(['documentType', 'createdBy'])->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Teléfono',
            'Número de documento',
            'Tipo de Documento',
            'Dirección',  
            'Estado',
            'Fecha de Creación',
            'Última Actualización',
            'Creado por',
        ];
    }

    public function map($client): array
    {
        return [
            $client->name,
            $client->email ?? '-',  
            $client->phone ?? '-',
            $client->document_number,
            $client->documentType->name ?? '-',  // Nombre del tipo de documento o guion
            $client->address ?? '-',  // Dirección o guion si es nula
            $client->status,
            $client->created_at->format('Y-m-d'),
            $client->updated_at->format('Y-m-d'),
            $client->createdBy->name ?? '-',  // Nombre del creador o guion
        ];
    }
}
