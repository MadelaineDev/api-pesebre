<?php

namespace App\Http\Controllers;

use App\Http\Requests\CrearAnimalRequest;
use App\Http\Requests\FiltroAnimalesRequest;
use App\Services\AnimalService;
use Illuminate\Http\JsonResponse;

class AnimalController extends Controller
{
    public function __construct(private readonly AnimalService $servicio)
    {
    }

    public function store(CrearAnimalRequest $request): JsonResponse
    {
        return response()->json($this->servicio->crear($request->validated())->toApi(), 201);
    }

    public function index(FiltroAnimalesRequest $request): JsonResponse
    {
        return response()->json($this->servicio->listar($request->query('especie'))->map->toApi()->values());
    }

    public function alimentacion(int $id): JsonResponse
    {
        $resultado = $this->servicio->alimentar($id);

        return response()->json([
            'mensaje' => 'Animal alimentado',
            'animal' => $resultado['animal']->toApi(),
            'alimentoRestante' => $resultado['alimento']->toApi(),
        ]);
    }
}
