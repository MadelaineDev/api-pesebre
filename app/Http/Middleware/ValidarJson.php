<?php

namespace App\Http\Middleware;

use App\Support\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rechaza con 400 un cuerpo que no es JSON válido y fuerza respuestas JSON.
 */
class ValidarJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $contenido = $request->getContent();
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true) && trim($contenido) !== '') {
            json_decode($contenido);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ErrorResponse::make($request, 400, 'FORMATO_INVALIDO', 'El cuerpo de la petición no es un JSON válido', [
                    ['error' => json_last_error_msg()],
                ]);
            }
        }

        return $next($request);
    }
}
