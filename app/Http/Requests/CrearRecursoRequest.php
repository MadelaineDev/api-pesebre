<?php

namespace App\Http\Requests;

use App\Enums\CategoriaRecurso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Valida SOLO el formato (400). Las reglas de negocio están en los servicios. */
class CrearRecursoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:60'],
            'categoria' => ['required', Rule::enum(CategoriaRecurso::class)],
            'cantidad' => ['required', 'integer', 'min:0'],
            'stockMinimo' => ['required', 'integer', 'min:1'],
        ];
    }
}
