<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Formato ÚNICO de error de toda la API.
 */
class ErrorResponse
{
    public static function make(Request $request, int $status, string $codigo, string $mensaje, array $detalles = []): JsonResponse
    {
        return response()->json([
            'codigo' => $codigo,
            'mensaje' => $mensaje,
            'detalles' => $detalles,
            'timestamp' => now('UTC')->format('Y-m-d\TH:i:s\Z'),
            'ruta' => '/'.ltrim($request->path(), '/'),
        ], $status, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
