<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado, verificar que tenga una cuenta seleccionada
        if (auth()->check()) {
            $user = auth()->user();
            
            // Si no tiene account_id, redirigir a seleccionar cuenta
            if (!$user->account_id && !$user->hasRole('admin')) {
                return redirect()->route('account.select')
                    ->with('error', 'Por favor selecciona una cuenta para continuar.');
            }
        }

        return $next($request);
    }
}
