<?php
/**
 * MOCK de MercadoBelenService (SOAP heredado) para desarrollo y pruebas.
 *
 * Levantar:  php -S 127.0.0.1:8081 mock-soap/server.php
 *
 * Comportamiento según <mb:cantidad>:
 *   999         -> soap:Fault (faultcode soap:Server, "Mercado cerrado")
 *   998         -> tarda 5 s en responder (para probar el timeout de 3 s)
 *   >= 500      -> <mb:resultado>SIN_EXISTENCIAS</mb:resultado>
 *   otro valor  -> <mb:resultado>DISPONIBLE</mb:resultado>
 */

$cuerpo = file_get_contents('php://input');

header('Content-Type: text/xml; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || trim($cuerpo) === '') {
    echo "<info>MercadoBelenService mock activo. Envie un POST SOAP ConsultarExistencias.</info>";
    return;
}

$categoria = preg_match('#<mb:categoria>\s*([^<]+?)\s*</mb:categoria>#', $cuerpo, $m) ? $m[1] : '';
$cantidad = preg_match('#<mb:cantidad>\s*(-?\d+)\s*</mb:cantidad>#', $cuerpo, $m) ? (int) $m[1] : 0;

error_log("[MercadoBelen] ConsultarExistencias categoria={$categoria} cantidad={$cantidad}");

if ($cantidad === 999) {
    http_response_code(500);
    echo <<<XML
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <soap:Fault>
      <faultcode>soap:Server</faultcode>
      <faultstring>Mercado cerrado</faultstring>
    </soap:Fault>
  </soap:Body>
</soap:Envelope>
XML;
    return;
}

if ($cantidad === 998) {
    sleep(5);
}

$resultado = $cantidad >= 500 ? 'SIN_EXISTENCIAS' : 'DISPONIBLE';

echo <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mb="http://belen.beatlech/mercado">
  <soapenv:Body>
    <mb:ConsultarExistenciasResponse>
      <mb:resultado>{$resultado}</mb:resultado>
    </mb:ConsultarExistenciasResponse>
  </soapenv:Body>
</soapenv:Envelope>
XML;
