<?php

namespace App\Repositories;

use App\Enums\CategoriaRecurso;
use App\Models\Recurso;
use Illuminate\Support\Collection;

/**
 * Capa de datos: único lugar que habla con la tabla "recursos".
 */
class RecursoRepository
{
    public function listar(?string $categoria = null, ?string $estado = null): Collection
    {
        return Recurso::query()
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderBy('id')
            ->get();
    }

    public function buscar(int $id): ?Recurso
    {
        return Recurso::find($id);
    }

    public function buscarParaActualizar(int $id): ?Recurso
    {
        return Recurso::whereKey($id)->lockForUpdate()->first();
    }

    public function existeNombre(string $nombre): bool
    {
        return Recurso::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->exists();
    }

    /** Recurso de alimento con más unidades (el que se usa para alimentar). */
    public function alimentoPrincipal(): ?Recurso
    {
        return Recurso::where('categoria', CategoriaRecurso::ALIMENTO_ANIMALES->value)
            ->orderByDesc('cantidad')
            ->lockForUpdate()
            ->first();
    }

    public function guardar(Recurso $recurso): Recurso
    {
        $recurso->save();

        return $recurso;
    }

    public function eliminar(Recurso $recurso): void
    {
        $recurso->delete();
    }
}
