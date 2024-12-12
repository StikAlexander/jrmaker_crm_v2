<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WompiController extends Controller
{
    /**
     * Maneja la redirección después del proceso de pago.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleRedirect(Request $request)
    {
        // Registrar la redirección para fines de depuración
        Log::info('Redirección recibida de Wompi.', $request->all());

        // Redirigir directamente a la página de inicio
        return redirect('https://jrmaker.com.co/');
    }
}