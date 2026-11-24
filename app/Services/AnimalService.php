<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Animal;
use App\Repositories\AnimalRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Capa de servicios: lógica de negocio de los animales (reglas 3 y 4).
 */
class AnimalService
{
    public function __construct(
        private readonly AnimalRepository $animales,
        private readonly RecursoService $recursoService,
    ) {
    }

    public function hoy(): string
    {
        return now(config('pesebre.zona_horaria'))->toDateString();
    }

    public function crear(array $datos): Animal
    {
        return $this->animales->guardar(new Animal([
            'nombre' => $datos['nombre'],
            'especie' => $datos['especie'],
            'racion_diaria' => $datos['racionDiaria'],
        ]));
    }

    public function listar(?string $especie): Collection
    {
        return $this->animales->listar($especie);
    }

    /** @return array{animal: Animal, alimento: \App\Models\Recurso} */
    public function alimentar(int $id): array
    {
        // Todo en una transacción: si algo falla no se descuenta nada (regla 3).
        return DB::transaction(function () use ($id) {
            $animal = $this->animales->buscarParaActualizar($id)
                ?? throw ApiException::noEncontrado("No existe el animal con id {$id}");

            $hoy = $this->hoy();
            if ($animal->ultima_alimentacion?->toDateString() === $hoy) { // Regla 4
                throw ApiException::reglaNegocio("{$animal->nombre} ya fue alimentado hoy ({$hoy})");
            }

            $alimento = $this->recursoService->descontarAlimento($animal->racion_diaria); // Regla 3

            $animal->ultima_alimentacion = $hoy;
            $this->animales->guardar($animal);

            return ['animal' => $animal, 'alimento' => $alimento];
        });
    }
}
