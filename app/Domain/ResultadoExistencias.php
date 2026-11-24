<?php

namespace App\Domain;

/**
 * Objeto de dominio que devuelve MercadoAdapter.
 * El resto del sistema trabaja con esto, nunca con XML.
 */
enum ResultadoExistencias: string
{
    case DISPONIBLE = 'DISPONIBLE';
    case SIN_EXISTENCIAS = 'SIN_EXISTENCIAS';
}
