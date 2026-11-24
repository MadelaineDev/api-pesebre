<?php

namespace App\Models;

use App\Enums\CategoriaRecurso;
use App\Enums\EstadoRecurso;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de datos RecursoPesebre (capa de datos).
 */
class Recurso extends Model
{
    protected $table = 'recursos';

    protected $fillable = ['nombre', 'categoria', 'cantidad', 'stock_minimo', 'estado'];

    protected $casts = [
        'categoria' => CategoriaRecurso::class,
        'estado' => EstadoRecurso::class,
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
    ];

    /** Representación JSON con los nombres de campo de la especificación. */
    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'categoria' => $this->categoria->value,
            'cantidad' => $this->cantidad,
            'stockMinimo' => $this->stock_minimo,
            'estado' => $this->estado->value,
        ];
    }
}
