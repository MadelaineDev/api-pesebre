<?php

use App\Exceptions\ApiException;
use App\Exceptions\MercadoNoDisponibleException;
use App\Http\Middleware\ValidarJson;
use App\Http\Middleware\VerificarRol;
use App\Support\ErrorResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware de seguridad
        $middleware->alias(['rol' => VerificarRol::class]);
        $middleware->api(prepend: [ValidarJson::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ===== Manejador GLOBAL de errores: un único formato JSON =====
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);
        $exceptions->dontReport([ApiException::class]);

        $exceptions->render(fn (ApiException $e, Request $r) => ErrorResponse::make($r, $e->status, $e->codigo, $e->getMessage(), $e->detalles));

        $exceptions->render(function (ValidationException $e, Request $r) {
            $detalles = collect($e->errors())
                ->flatMap(fn ($mensajes, $campo) => collect($mensajes)->map(fn ($m) => ['campo' => $campo, 'mensaje' => $m]))
                ->values()->all();

            return ErrorResponse::make($r, 400, 'FORMATO_INVALIDO', 'Los datos enviados no tienen un formato válido', $detalles);
        });

        $exceptions->render(fn (NotFoundHttpException $e, Request $r) => ErrorResponse::make($r, 404, 'NO_ENCONTRADO', 'La ruta o el recurso solicitado no existe'));

        $exceptions->render(fn (MethodNotAllowedHttpException $e, Request $r) => ErrorResponse::make($r, 405, 'METODO_NO_PERMITIDO', 'Método HTTP no permitido en esta ruta'));

        // Red de seguridad: si una caída del servicio externo escapara del servicio -> 503, nunca 500
        $exceptions->render(fn (MercadoNoDisponibleException $e, Request $r) => ErrorResponse::make($r, 503, 'SERVICIO_EXTERNO_CAIDO', 'El Mercado de Belén no está disponible', [['motivo' => $e->getMessage()]]));

        $exceptions->render(fn (HttpExceptionInterface $e, Request $r) => ErrorResponse::make($r, $e->getStatusCode(), 'ERROR_HTTP', $e->getMessage() ?: 'Error HTTP'));

        $exceptions->render(fn (Throwable $e, Request $r) => ErrorResponse::make($r, 500, 'ERROR_INTERNO', 'Ocurrió un error inesperado', config('app.debug') ? [['excepcion' => class_basename($e), 'detalle' => $e->getMessage()]] : []));
    })->create();
