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
        // Obtener solo los usuarios con el rol de cliente
        return User::role('client')->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Teléfono',
            'Número de documento',
            'Estado',
        ];
    }

    public function map($client): array
    {
        return [
            $client->name,
            $client->email,
            $client->phone,
            $client->document_number,
            $client->status,
        ];
    }
}
