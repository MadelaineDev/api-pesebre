<?php

namespace App\Http\Requests;

use App\Enums\CategoriaRecurso;
use App\Enums\EstadoRecurso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroRecursosRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'categoria' => ['sometimes', Rule::enum(CategoriaRecurso::class)],
            'estado' => ['sometimes', Rule::enum(EstadoRecurso::class)],
        ];
    }
}
