<?php

namespace App\Imports;

use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InvoiceImport implements ToModel, WithValidation, WithHeadingRow
{
    public function model(array $row)
    {
        // Manejo de fecha, convierte si es numérica o parsea si es texto
        $issueDate = $this->parseDate($row['issue_date']);

        // Eliminar el prefijo del número de factura
        $invoiceNumber = preg_replace('/^FEVD/', '', $row['invoice_number']);

        // Busca el cliente por el número de documento
        $clientId = User::where('document_number', $row['document_number'])->value('id');

        // Si el cliente no es encontrado, lanzar una excepción
        if (!$clientId) {
            throw new \Exception("No se encontró un cliente con el número de documento: " . $row['document_number']);
        }

        // Crear la factura
        return new Invoice([
            'invoice_number' => $invoiceNumber,  // Guardar solo el número sin el prefijo
            'issue_date' => $issueDate,
            'due_date' => $issueDate->copy()->addDays(30),
            'total_amount' => $row['total_amount'],
            'client_id' => $clientId,
            'created_by' => auth()->id(),
            'description' => $row['description'] ?? '',
        ]);
    }

    /**
     * Método para manejar la conversión de la fecha.
     *
     * @param mixed $date
     * @return Carbon
     */
    protected function parseDate($date)
    {
        try {
            return Carbon::createFromFormat('m/d/Y', $date);
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date);
            } catch (\Exception $e) {
                if (is_numeric($date)) {
                    return Carbon::instance(ExcelDate::excelToDateTimeObject($date));
                } else {
                    return Carbon::parse($date);
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'issue_date' => 'required',
            'total_amount' => 'required|numeric|min:0',
            'document_number' => 'required',
        ];
    }
}
