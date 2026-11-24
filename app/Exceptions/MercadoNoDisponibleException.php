<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * El servicio SOAP externo respondió con Fault, no respondió a tiempo
 * o devolvió algo ilegible. La lanza solo MercadoAdapter.
 */
class MercadoNoDisponibleException extends RuntimeException
{
}
