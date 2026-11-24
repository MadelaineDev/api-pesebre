<?php

namespace App\Enums;

enum EstadoRecurso: string
{
    case DISPONIBLE = 'DISPONIBLE';
    case BAJO_STOCK = 'BAJO_STOCK';
    case AGOTADO = 'AGOTADO';
    case REABASTECIMIENTO_PENDIENTE = 'REABASTECIMIENTO_PENDIENTE';
}
