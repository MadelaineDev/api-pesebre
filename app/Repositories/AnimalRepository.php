<?php

namespace App\Repositories;

use App\Models\Animal;
use Illuminate\Support\Collection;

/**
 * Capa de datos: único lugar que habla con la tabla "animales".
 */
class AnimalRepository
{
    public function listar(?string $especie = null): Collection
    {
        return Animal::query()
            ->when($especie, fn ($q) => $q->where('especie', $especie))
            ->orderBy('id')
            ->get();
    }

    public function buscarParaActualizar(int $id): ?Animal
    {
        return Animal::whereKey($id)->lockForUpdate()->first();
    }

    public function sinAlimentarHoy(string $hoy): Collection
    {
        return Animal::query()
            ->where(fn ($q) => $q->whereNull('ultima_alimentacion')->orWhereDate('ultima_alimentacion', '<', $hoy))
            ->orderBy('id')
            ->get();
    }

    public function guardar(Animal $animal): Animal
    {
        $animal->save();

        return $animal;
    }
}
