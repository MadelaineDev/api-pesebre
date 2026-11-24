<?php

return [
    // Tokens de prueba por rol (API key). Valores reales SOLO en .env.
    'tokens' => [
        'ENCARGADO_PESEBRE' => env('TOKEN_ENCARGADO_PESEBRE'),
        'PASTOR' => env('TOKEN_PASTOR'),
    ],

    'mercado' => [
        'url' => env('MERCADO_SOAP_URL', 'http://127.0.0.1:8081/MercadoBelenService'),
        'timeout' => (int) env('MERCADO_TIMEOUT_SEGUNDOS', 3),
        'intentos' => (int) env('MERCADO_INTENTOS', 2),
        'backoff_ms' => (int) env('MERCADO_BACKOFF_MS', 200),
    ],

    'zona_horaria' => env('PESEBRE_ZONA_HORARIA', 'America/Guayaquil'),
];
