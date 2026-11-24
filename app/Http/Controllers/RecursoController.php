<?php

namespace App\Http\Controllers;

use App\Http\Requests\CantidadRequest;
use App\Http\Requests\CrearRecursoRequest;
use App\Http\Requests\FiltroRecursosRequest;
use App\Services\RecursoService;
use Illuminate\Http\JsonResponse;

/**
 * Capa de controladores: recibe HTTP, delega al servicio y arma la respuesta.
 * NO contiene reglas de negocio.
 */
class RecursoController extends Controller
{
    public function __construct(private readonly RecursoService $servicio)
    {
    }

    public function store(CrearRecursoRequest $request): JsonResponse
    {
        $recurso = $this->servicio->crear($request->validated());

        return response()->json($recurso->toApi(), 201);
    }

    public function index(FiltroRecursosRequest $request): JsonResponse
    {
        $recursos = $this->servicio->listar($request->query('categoria'), $request->query('estado'));

        return response()->json($recursos->map->toApi()->values());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->servicio->obtener($id)->toApi());
    }

    public function consumo(CantidadRequest $request, int $id): JsonResponse
    {
        $recurso = $this->servicio->consumir($id, (int) $request->validated('cantidad'));

        return response()->json($recurso->toApi());
    }

    public function reabastecimiento(CantidadRequest $request, int $id): JsonResponse
    {
        $resultado = $this->servicio->reabastecer($id, (int) $request->validated('cantidad'));

        if ($resultado['pendiente']) {
            return response()->json([
                'mensaje' => 'El Mercado de Belén no está disponible. La solicitud quedó registrada y se confirmará después.',
                'motivo' => $resultado['motivo'],
                'recurso' => $resultado['recurso']->toApi(),
            ], 202);
        }

        return response()->json([
            'mensaje' => 'Reabastecimiento confirmado por el Mercado de Belén',
            'recurso' => $resultado['recurso']->toApi(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->servicio->eliminar($id);

        return response()->json(null, 204);
    }
}
