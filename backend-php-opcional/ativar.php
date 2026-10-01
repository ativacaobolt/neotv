<?php
/**
 * Proxy de ativação de dispositivo.
 *
 * Recebe do front-end apenas os dados do cliente final (usuário, senha e
 * MAC do dispositivo). O código/token da parceria fica só aqui no
 * servidor e é anexado antes de repassar a chamada ao upstream — o
 * cliente nunca vê essas credenciais.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function responder(int $status, bool $ok, string $mensagem, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['ok' => $ok, 'mensagem' => $mensagem], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, false, 'Método não permitido.');
}

$config = require __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);

if (!is_array($payload)) {
    responder(400, false, 'Requisição inválida.');
}

$usuario = isset($payload['usuario']) ? trim((string) $payload['usuario']) : '';
$senha   = isset($payload['senha'])   ? trim((string) $payload['senha'])   : '';
$mac     = isset($payload['mac'])     ? trim((string) $payload['mac'])     : '';

if ($usuario === '' || $senha === '') {
    responder(422, false, 'Informe usuário e senha.');
}

if ($mac === '' || !preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) {
    responder(422, false, 'Endereço MAC inválido.');
}

// Monta o corpo para o upstream, injetando as credenciais da parceria
// que nunca trafegam até o navegador do cliente final.
$corpoUpstream = json_encode([
    'usuario' => $usuario,
    'senha'   => $senha,
    'mac'     => $mac,
    'codigo'  => $config['codigo'],
    'token'   => $config['token'],
], JSON_UNESCAPED_UNICODE);

$ch = curl_init($config['upstream_url']);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $corpoUpstream,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => $config['timeout'],
    CURLOPT_SSL_VERIFYPEER => true,
]);

$respostaBruta = curl_exec($ch);
$erroCurl      = curl_error($ch);
$statusHttp    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($respostaBruta === false) {
    error_log('[ativar.php] Falha ao contatar upstream: ' . $erroCurl);
    responder(502, false, 'Erro ao comunicar com o servidor de ativação.');
}

$respostaUpstream = json_decode($respostaBruta, true);

if (!is_array($respostaUpstream)) {
    error_log('[ativar.php] Resposta upstream inesperada (HTTP ' . $statusHttp . '): ' . $respostaBruta);
    responder(502, false, 'Resposta inesperada do servidor de ativação.');
}

// Repassa só o essencial ao cliente — nunca o token/código, nem qualquer
// outro campo interno que o upstream eventualmente inclua na resposta.
$ok       = (bool) ($respostaUpstream['ok'] ?? false);
$mensagem = (string) ($respostaUpstream['mensagem'] ?? ($ok ? 'Dispositivo ativado.' : 'Não foi possível ativar o dispositivo.'));

responder($ok ? 200 : 400, $ok, $mensagem);
