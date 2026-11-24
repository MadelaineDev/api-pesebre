<?php

namespace App\Http\Requests;

use App\Enums\Especie;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrearAnimalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:1', 'max:60'],
            'especie' => ['required', Rule::enum(Especie::class)],
            'racionDiaria' => ['required', 'integer', 'between:1,10'],
        ];
    }
}
