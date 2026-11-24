<?php

namespace App\Http\Middleware;

use App\Support\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de seguridad (API key en el header Authorization).
 *   Sin token            -> 401
 *   Token desconocido    -> 401
 *   Token de otro rol    -> 403
 * Los tokens se leen de variables de entorno (config/pesebre.php), nunca del código.
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string $rolRequerido): Response
    {
        $header = trim((string) $request->header('Authorization', ''));
        $token = trim(preg_replace('/^(Bearer|ApiKey)\s+/i', '', $header));

        if ($token === '') {
            return ErrorResponse::make($request, 401, 'NO_AUTENTICADO', 'Falta el token en el header Authorization');
        }

        $rol = null;
        foreach (config('pesebre.tokens') as $nombreRol => $valor) {
            if (is_string($valor) && $valor !== '' && hash_equals($valor, $token)) {
                $rol = $nombreRol;
            }
        }

        if ($rol === null) {
            return ErrorResponse::make($request, 401, 'TOKEN_INVALIDO', 'El token enviado no es válido');
        }

        if ($rol !== $rolRequerido) {
            return ErrorResponse::make($request, 403, 'ROL_INSUFICIENTE', "Se requiere el rol {$rolRequerido}; el token pertenece al rol {$rol}");
        }

        $request->attributes->set('rol', $rol);

        return $next($request);
    }
}
