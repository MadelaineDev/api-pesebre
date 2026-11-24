<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Cuerpo {"cantidad": n} de consumo y reabastecimiento. */
class CantidadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cantidad' => ['required', 'integer', 'min:1'],
        ];
    }
}
