<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTipo
{
    /**
     * Manejar la solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$tipos
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$tipos): Response
    {
        // Verificar si hay un usuario autenticado
        if (!$request->user()) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        // Ahora usamos el campo "tipo" en lugar de "role"
        $userTipo = $request->user()->tipo;

        // Verificar si el tipo del usuario está dentro de los permitidos
        if (!in_array($userTipo, $tipos)) {
            return response()->json(['error' => 'No tienes permiso para acceder a este recurso'], 403);
        }

        return $next($request);
    }
}
