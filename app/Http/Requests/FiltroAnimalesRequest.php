<?php

namespace App\Http\Requests;

use App\Enums\Especie;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroAnimalesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'especie' => ['sometimes', Rule::enum(Especie::class)],
        ];
    }
}
