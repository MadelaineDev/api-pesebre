<?php

namespace App\Services;

use App\Enums\EstadoRecurso;
use App\Repositories\AnimalRepository;
use App\Repositories\RecursoRepository;

/**
 * Funcionalidad opcional: resumen del pesebre.
 */
class PesebreService
{
    public function __construct(
        private readonly RecursoRepository $recursos,
        private readonly AnimalRepository $animales,
        private readonly AnimalService $animalService,
    ) {
    }

    public function resumen(): array
    {
        $todos = $this->recursos->listar();
        $criticos = $todos->filter(fn ($r) => in_array($r->estado, [EstadoRecurso::BAJO_STOCK, EstadoRecurso::AGOTADO], true));
        $hayAgotados = $todos->contains(fn ($r) => $r->estado === EstadoRecurso::AGOTADO);

        return [
            'recursosCriticos' => $criticos->map->toApi()->values(),
            'animalesSinAlimentarHoy' => $this->animales->sinAlimentarHoy($this->animalService->hoy())->map->toApi()->values(),
            'pesebreListo' => ! $hayAgotados,
        ];
    }
}
