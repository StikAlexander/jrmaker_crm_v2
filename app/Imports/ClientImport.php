<?php

namespace App\Imports;

use App\Models\User;
use App\Models\DocumentType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientImport implements ToModel, WithValidation, WithHeadingRow
{
    public function model(array $row)
    {
        // Obtener el ID del tipo de documento usando el nombre proporcionado en el archivo Excel
        $documentType = DocumentType::where('name', $row['document_type'])->first();

        // Validar si el tipo de documento existe
        if (!$documentType) {
            throw new \Exception("Tipo de documento no encontrado: " . $row['document_type']);
        }

        // Crear un nuevo cliente (sin contraseña, porque no se usa)
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'document_number' => $row['document_number'],
            'document_type_id' => $documentType->id,  // Asociar el tipo de documento por ID
            'status' => $row['status'] ?? 'active',  // Estado por defecto 'active'
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'document_number' => 'required|unique:users,document_number',
            'document_type' => 'required|exists:document_types,name',  // Validación del nombre del tipo de documento
            'status' => 'nullable|in:active,inactive,suspended',  // Estado opcional
        ];
    }
}
