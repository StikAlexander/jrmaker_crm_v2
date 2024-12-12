<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectToInvoices
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica si el usuario está en la raíz del panel del cliente
        if ($request->is('client') || $request->is('client/')) {
            return redirect('/client/client-invoices');
        }

        return $next($request);
    }
}
