<?php
/**
 * Configuração da parceria (credenciais da API upstream).
 *
 * Nunca exponha estes valores ao front-end. Prefira sobrescrevê-los via
 * variáveis de ambiente no servidor (ex.: Apache SetEnv / php-fpm pool /
 * painel de hospedagem) em vez de deixar o valor fixo neste arquivo em
 * produção.
 */

return [
    // Endpoint da API upstream que efetivamente ativa o dispositivo.
    'upstream_url' => getenv('ATIVACAO_UPSTREAM_URL') ?: 'https://boltplayapp.com.br/parceria/ativar',

    // Identificador da parceria/revenda junto ao upstream.
    'codigo' => getenv('ATIVACAO_CODIGO') ?: 'titan',

    // Token fixo da parceria. Mantenha em segredo — nunca retorne ao cliente.
    'token' => getenv('ATIVACAO_TOKEN') ?: 'UHh2NsIllE8H.58956102fd98d9f0',

    // Timeout (segundos) para a chamada ao upstream.
    'timeout' => 15,
];
