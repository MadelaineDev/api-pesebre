<?php

namespace App\Adapters;

use App\Domain\ResultadoExistencias;
use App\Enums\CategoriaRecurso;
use App\Exceptions\MercadoNoDisponibleException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Adaptador del servicio SOAP heredado MercadoBelenService.
 *
 * - Es la ÚNICA clase que conoce XML / SOAP.
 * - Traduce: (categoria, cantidad) -> XML -> ResultadoExistencias.
 * - Punto de gestión del fallo: timeout de 3 s, soap:Fault o respuesta
 *   ilegible => MercadoNoDisponibleException (tras los reintentos).
 */
class MercadoAdapter
{
    private const NS_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';
    private const NS_MB = 'http://belen.beatlech/mercado';

    public function __construct(
        private readonly string $url,
        private readonly int $timeoutSegundos = 3,
        private readonly int $intentos = 2,
        private readonly int $backoffMs = 200,
    ) {
    }

    /**
     * @throws MercadoNoDisponibleException
     */
    public function consultarExistencias(CategoriaRecurso $categoria, int $cantidad): ResultadoExistencias
    {
        $xml = $this->construirRequest($categoria, $cantidad);
        $ultimoError = 'sin respuesta';

        for ($intento = 1; $intento <= $this->intentos; $intento++) {
            try {
                $respuesta = Http::withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'SOAPAction' => 'ConsultarExistencias',
                ])
                    ->timeout($this->timeoutSegundos)
                    ->connectTimeout($this->timeoutSegundos)
                    ->withBody($xml, 'text/xml; charset=utf-8')
                    ->post($this->url);

                return $this->interpretarRespuesta($respuesta->body());
            } catch (MercadoNoDisponibleException $e) {
                $ultimoError = $e->getMessage();
            } catch (Throwable $e) { // timeout, conexión rechazada, etc.
                Log::debug('MercadoBelenService error técnico: '.$e->getMessage());
                $ultimoError = 'Sin respuesta del Mercado de Belén en '.$this->timeoutSegundos.' s';
            }

            Log::warning("MercadoBelenService intento {$intento}/{$this->intentos} falló: {$ultimoError}");

            if ($intento < $this->intentos) {
                usleep($this->backoffMs * 1000 * (2 ** ($intento - 1))); // backoff exponencial
            }
        }

        throw new MercadoNoDisponibleException($ultimoError);
    }

    private function construirRequest(CategoriaRecurso $categoria, int $cantidad): string
    {
        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mb="http://belen.beatlech/mercado">
  <soapenv:Body>
    <mb:ConsultarExistenciasRequest>
      <mb:categoria>{$categoria->value}</mb:categoria>
      <mb:cantidad>{$cantidad}</mb:cantidad>
    </mb:ConsultarExistenciasRequest>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * @throws MercadoNoDisponibleException
     */
    private function interpretarRespuesta(string $cuerpo): ResultadoExistencias
    {
        $previo = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($cuerpo);
        libxml_use_internal_errors($previo);

        if ($doc === false) {
            throw new MercadoNoDisponibleException('Respuesta SOAP ilegible');
        }

        $doc->registerXPathNamespace('soap', self::NS_SOAP);
        $doc->registerXPathNamespace('mb', self::NS_MB);

        $fault = $doc->xpath('//soap:Fault');
        if (! empty($fault)) {
            $faultstring = (string) ($fault[0]->faultstring ?? 'Fault');
            throw new MercadoNoDisponibleException('soap:Fault - '.$faultstring);
        }

        $resultado = $doc->xpath('//mb:resultado');
        $valor = isset($resultado[0]) ? trim((string) $resultado[0]) : '';

        return ResultadoExistencias::tryFrom($valor)
            ?? throw new MercadoNoDisponibleException("Resultado desconocido: '{$valor}'");
    }
}
