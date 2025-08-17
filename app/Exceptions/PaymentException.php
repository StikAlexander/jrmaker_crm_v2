<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    /**
     * Constructor de la excepción.
     *
     * @param string $message Mensaje de error
     * @param int $code Código de error
     * @param \Throwable|null $previous Excepción previa
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Reporte la excepción.
     *
     * @return bool|null
     */
    public function report()
    {
        // Ya estamos registrando en el servicio, por lo que podemos evitar el registro duplicado
        return true;
    }

    /**
     * Renderiza la excepción en una respuesta HTTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Error en el procesamiento del pago',
                'message' => $this->getMessage(),
            ], 422);
        }

        // Si es una solicitud de Livewire o Filament
        if ($request->header('X-Livewire') || $request->header('X-Filament')) {
            session()->flash('error', 'Error en el procesamiento del pago: ' . $this->getMessage());
            return back();
        }

        // Redirigir con mensaje de error para solicitudes web normales
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['payment' => $this->getMessage()]);
    }
}
