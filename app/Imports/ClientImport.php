<?php

namespace App\Imports;

use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class ClientImport implements ToModel, WithValidation, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info("Fila procesada: ", $row);

        // Verificar que 'document_type' no sea null antes de usar trim
        $documentTypeName = !is_null($row['document_type']) ? trim($row['document_type']) : null;
        $documentType = DocumentType::where('name', $documentTypeName)->first();

        if (!$documentType) {
            Log::error("Tipo de documento no encontrado: " . $documentTypeName);
            throw new \Exception("Tipo de documento no encontrado: " . $documentTypeName);
        }

        // Crear el usuario
        $user = new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'document_number' => $row['document_number'],
            'document_type_id' => $documentType->id,
            'status' => $row['status'] ?? 'active',
            'address' => $row['address'] ?? null,
            'created_by_id' => Auth::id(),
        ]);

        $user->save();

        // Asignar los roles: cliente y panel_user
        $user->assignRole(['panel_user', 'client']);

        return $user;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'document_number' => 'required|unique:users,document_number',
            'document_type' => 'required|exists:document_types,name',
            'status' => 'nullable|in:active,inactive,suspended',
            'address' => 'nullable|string|max:255',
        ];
    }
}
