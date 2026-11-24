<?php

namespace App\Services;

use App\Adapters\MercadoAdapter;
use App\Domain\ResultadoExistencias;
use App\Enums\CategoriaRecurso;
use App\Enums\EstadoRecurso;
use App\Exceptions\ApiException;
use App\Exceptions\MercadoNoDisponibleException;
use App\Models\Recurso;
use App\Repositories\RecursoRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Capa de servicios: TODA la lógica y reglas de negocio de los recursos.
 */
class RecursoService
{
    public function __construct(
        private readonly RecursoRepository $recursos,
        private readonly MercadoAdapter $mercado,
    ) {
    }

    /** Regla 1: el estado lo calcula el sistema en cada cambio. */
    public function calcularEstado(int $cantidad, int $stockMinimo): EstadoRecurso
    {
        if ($cantidad === 0) {
            return EstadoRecurso::AGOTADO;
        }
        if ($cantidad < $stockMinimo) {
            return EstadoRecurso::BAJO_STOCK;
        }

        return EstadoRecurso::DISPONIBLE;
    }

    public function crear(array $datos): Recurso
    {
        if ($this->recursos->existeNombre($datos['nombre'])) {
            throw ApiException::conflicto("Ya existe un recurso con el nombre '{$datos['nombre']}'", [
                ['campo' => 'nombre', 'mensaje' => 'El nombre debe ser único'],
            ]);
        }

        $recurso = new Recurso([
            'nombre' => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'cantidad' => $datos['cantidad'],
            'stock_minimo' => $datos['stockMinimo'],
        ]);
        $recurso->estado = $this->calcularEstado($recurso->cantidad, $recurso->stock_minimo);

        return $this->recursos->guardar($recurso);
    }

    public function listar(?string $categoria, ?string $estado): Collection
    {
        return $this->recursos->listar($categoria, $estado);
    }

    public function obtener(int $id): Recurso
    {
        return $this->recursos->buscar($id)
            ?? throw ApiException::noEncontrado("No existe el recurso con id {$id}");
    }

    /** Reglas 1 y 2. */
    public function consumir(int $id, int $cantidad): Recurso
    {
        return DB::transaction(function () use ($id, $cantidad) {
            $recurso = $this->recursos->buscarParaActualizar($id)
                ?? throw ApiException::noEncontrado("No existe el recurso con id {$id}");

            $nuevaCantidad = $recurso->cantidad - $cantidad;
            if ($nuevaCantidad < 0) { // Regla 2
                throw ApiException::conflicto(
                    "Consumo rechazado: se piden {$cantidad} unidades y solo hay {$recurso->cantidad}",
                    [['disponible' => $recurso->cantidad, 'solicitado' => $cantidad]],
                );
            }

            $recurso->cantidad = $nuevaCantidad;
            $recurso->estado = $this->calcularEstado($nuevaCantidad, $recurso->stock_minimo); // Regla 1

            return $this->recursos->guardar($recurso);
        });
    }

    /**
     * Regla 5 + manejo de fallos distribuidos.
     *
     * @return array{recurso: Recurso, pendiente: bool, motivo: ?string}
     */
    public function reabastecer(int $id, int $cantidad): array
    {
        $recurso = $this->obtener($id);

        try {
            // Se consulta FUERA de la transacción para no bloquear la BD mientras se espera la red.
            $resultado = $this->mercado->consultarExistencias($recurso->categoria, $cantidad);
        } catch (MercadoNoDisponibleException $e) {
            // Fault o timeout: la cantidad NO cambia, el recurso queda pendiente y se responde 202.
            $recurso->estado = EstadoRecurso::REABASTECIMIENTO_PENDIENTE;
            $this->recursos->guardar($recurso);

            return ['recurso' => $recurso, 'pendiente' => true, 'motivo' => $e->getMessage()];
        }

        if ($resultado === ResultadoExistencias::SIN_EXISTENCIAS) {
            throw ApiException::reglaNegocio(
                "El Mercado de Belén no tiene existencias de {$recurso->categoria->value} para {$cantidad} unidades",
                [['categoria' => $recurso->categoria->value, 'solicitado' => $cantidad, 'mercado' => 'SIN_EXISTENCIAS']],
            );
        }

        $recurso = DB::transaction(function () use ($id, $cantidad) {
            $recurso = $this->recursos->buscarParaActualizar($id)
                ?? throw ApiException::noEncontrado("No existe el recurso con id {$id}");
            $recurso->cantidad += $cantidad;
            $recurso->estado = $this->calcularEstado($recurso->cantidad, $recurso->stock_minimo);

            return $this->recursos->guardar($recurso);
        });

        return ['recurso' => $recurso, 'pendiente' => false, 'motivo' => null];
    }

    /** Regla 6. */
    public function eliminar(int $id): void
    {
        $recurso = $this->obtener($id);

        if ($recurso->cantidad > 0) {
            throw ApiException::conflicto(
                "No se puede eliminar '{$recurso->nombre}': todavía tiene {$recurso->cantidad} unidades",
            );
        }

        $this->recursos->eliminar($recurso);
    }

    /** Descuenta alimento (usado por AnimalService dentro de su transacción). Regla 3. */
    public function descontarAlimento(int $racion): Recurso
    {
        $alimento = $this->recursos->alimentoPrincipal();

        if ($alimento === null || $alimento->cantidad < $racion) {
            throw ApiException::conflicto(
                'No hay alimento suficiente para la ración diaria',
                [['categoria' => CategoriaRecurso::ALIMENTO_ANIMALES->value, 'disponible' => $alimento?->cantidad ?? 0, 'requerido' => $racion]],
            );
        }

        $alimento->cantidad -= $racion;
        $alimento->estado = $this->calcularEstado($alimento->cantidad, $alimento->stock_minimo);

        return $this->recursos->guardar($alimento);
    }
}
