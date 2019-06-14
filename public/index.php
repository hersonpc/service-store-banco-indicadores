<?php
// No output errors
error_reporting(0);

// No cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// App info
header('X-APP: hersonpc/service-store-banco-indicadores');
header('X-API-VERSION: 0.0.1 beta (2019-06-13)');
header('X-AUTHOR: Herson Melo <hersonpc@gmail.com>');

require __DIR__ . '/../vendor/autoload.php';

use App\Stomp;

// Function Utils...
function env($varName, $default = '') {
  return(getenv($varName) ?: $default);
}

function error($message, $level = 'warning') {
  return(
    json_encode(
      [
        "status" => "error", 
        "error" => 
          [
            "level" => $level,
            "message" => $message
          ]
      ]
    )
  );
}

// Verificando se existe conectividade com o RabbitMQ...
if (!env("AMQP_SERVER") or !env("AMQP_PORT") or !env("AMQP_USER") or !env("AMQP_PASS")) {
  header("HTTP/1.1 500 Internal Server Error");
  echo error('ERROR - AMQP protocol not configured correctly.', 'critical');
  throw new Exception('ERROR - AMQP protocol not configured correctly.');
  exit();
}

$json = json_encode($_POST, JSON_PRETTY_PRINT);
$parsedPayload = json_decode(json_decode($json)->data);

try {
  $stomp = new Stomp(env("AMQP_SERVER"), env("AMQP_PORT"), env("AMQP_USER"), env("AMQP_PASS"),
      '/', 'banco.indicadores.store');
   $stomp->sendMessage($parsedPayload);
  header("HTTP/1.1 200 OK");
  echo json_encode(["status" => "ok", "payload" => $parsedPayload]);
} catch (Exception $e) {
  header("HTTP/1.1 500 Internal Server Error");
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}