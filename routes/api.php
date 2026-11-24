<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\PesebreController;
use App\Http\Controllers\RecursoController;
use Illuminate\Support\Facades\Route;

/*
| Capa de rutas. Prefijo /api (configurado en bootstrap/app.php).
| Protegidas: middleware rol:ENCARGADO_PESEBRE
*/

// ---- Recursos ----
Route::get('/recursos', [RecursoController::class, 'index']);
Route::get('/recursos/{id}', [RecursoController::class, 'show'])->whereNumber('id');

Route::middleware('rol:ENCARGADO_PESEBRE')->group(function () {
    Route::post('/recursos', [RecursoController::class, 'store']);
    Route::patch('/recursos/{id}/consumo', [RecursoController::class, 'consumo'])->whereNumber('id');
    Route::patch('/recursos/{id}/reabastecimiento', [RecursoController::class, 'reabastecimiento'])->whereNumber('id');
    Route::delete('/recursos/{id}', [RecursoController::class, 'destroy'])->whereNumber('id');
});

// ---- Animales (públicos) ----
Route::post('/animales', [AnimalController::class, 'store']);
Route::get('/animales', [AnimalController::class, 'index']);
Route::patch('/animales/{id}/alimentacion', [AnimalController::class, 'alimentacion'])->whereNumber('id');

// ---- Opcional ----
Route::get('/pesebre/resumen', [PesebreController::class, 'resumen']);
