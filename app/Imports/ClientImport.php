<?php

namespace App\Imports;

use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientImport implements ToModel, WithValidation, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info("Fila procesada: ", $row);
    
        $documentType = DocumentType::where('name', $row['document_type'])->first();
    
        if (!$documentType) {
            Log::error("Tipo de documento no encontrado: " . $row['document_type']);
            throw new \Exception("Tipo de documento no encontrado: " . $row['document_type']);
        }
    
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'document_number' => $row['document_number'],
            'document_type_id' => $documentType->id,
            'status' => $row['status'] ?? 'active',
            'address' => $row['address'] ?? null,
        ]);
    }
    

    public function rules(): array
    {
        Log::info("Validando fila...");
    
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
