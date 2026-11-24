<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Excepción de negocio. La lanzan los servicios y la traduce a JSON
 * el manejador global de errores (bootstrap/app.php).
 */
class ApiException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $codigo,
        string $mensaje,
        public readonly array $detalles = [],
    ) {
        parent::__construct($mensaje);
    }

    public static function noEncontrado(string $mensaje): self
    {
        return new self(404, 'NO_ENCONTRADO', $mensaje);
    }

    public static function conflicto(string $mensaje, array $detalles = []): self
    {
        return new self(409, 'CONFLICTO_ESTADO', $mensaje, $detalles);
    }

    public static function reglaNegocio(string $mensaje, array $detalles = []): self
    {
        return new self(422, 'REGLA_NEGOCIO', $mensaje, $detalles);
    }
}
