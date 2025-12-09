<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Maneja la petición.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles  // roles pasados como parámetros
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, redirigir al login
        if (!$user) {
            return redirect()->route('login');
        }

        // Recorremos los roles recibidos y continuamos si el usuario coincide con alguno
        foreach ($roles as $role) {
            // Ej.: campo "is_propietario", "is_barbero", "is_cliente" en la tabla users
            $fieldName = "is_$role";
            if (isset($user->$fieldName) && $user->$fieldName) {
                return $next($request);
            }
        }

        // Si no tiene ninguno de los roles requeridos
        abort(403, 'No tienes permiso para acceder a esta ruta.');
    }
}
