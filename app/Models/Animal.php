<?php

namespace App\Models;

use App\Enums\Especie;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de datos Animal (capa de datos).
 */
class Animal extends Model
{
    protected $table = 'animales';

    protected $fillable = ['nombre', 'especie', 'racion_diaria', 'ultima_alimentacion'];

    protected $casts = [
        'especie' => Especie::class,
        'racion_diaria' => 'integer',
        'ultima_alimentacion' => 'date',
    ];

    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'especie' => $this->especie->value,
            'racionDiaria' => $this->racion_diaria,
            'ultimaAlimentacion' => $this->ultima_alimentacion?->toDateString(),
        ];
    }
}
