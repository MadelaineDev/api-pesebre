<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'between' => ['numeric' => 'El campo :attribute debe estar entre :min y :max.'],
    'min' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual a :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'max' => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'enum' => 'El valor de :attribute no es válido.',
    'in' => 'El valor de :attribute no es válido.',
    'attributes' => [
        'stockMinimo' => 'stockMinimo',
        'racionDiaria' => 'racionDiaria',
    ],
];
