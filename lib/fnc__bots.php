<?php

/**
 * Limita o número de requisições por minuto para bots específicos.
 *
 * Bloqueio temporário: Bloqueia por 5 minutos após exceder o limite (configurável).
 * Armazenamento em arquivos temporários: Usa arquivos JSON para persistir dados entre requisições.
 * Chave única: Combina IP + tipo de bot para identificação precisa.
 * Limpeza automática: Remove dados antigos automaticamente.
 * Headers HTTP adequados: Usa Retry-After para informar quando tentar novamente.
 * Logs opcionales: Registra bloqueios no error log do PHP.*
 *
 * @param array $bots Lista de bots a serem monitorados.
 * @param int $maxRequests Número máximo de requisições por minuto.
 * @param int $blockDuration Duração do bloqueio em segundos (padrão: 300s = 5min).
 */
function bots_rate_limit($bots, $maxRequests = 10, $blockDuration = 300) {

    // Obtém o agente de usuário do cliente
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $clientIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';

    // Verifica se é um bot da lista
    $detectedBot = null;
    foreach ($bots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            $detectedBot = $bot;
            break;
        }
    }

    if (!$detectedBot) {
        return; // Não é um bot da lista, permite acesso
    }

    // Cria uma chave única baseada APENAS no tipo de bot (não no IP)

    $key = $detectedBot;
    $tempDir = 'log/';
    $rateLimitFile = $tempDir . 'bot_rate_limit_' . $key . '.json';
    $blockFile = $tempDir . 'bot_blocked_' . $key . '.json';

    $currentTime = time();
    $currentMinute = floor($currentTime / 60);

    // Verifica se o bot está bloqueado
    if (file_exists($blockFile)) {
        $blockData = json_decode(file_get_contents($blockFile), true);
        if ($blockData && $currentTime < $blockData['blocked_until']) {
            $remainingTime = $blockData['blocked_until'] - $currentTime;
            header("HTTP/1.1 429 Too Many Requests");
            header("Retry-After: $remainingTime");
            echo "429 Too Many Requests - Bot temporarily blocked. Try again in {$remainingTime} seconds.";
            exit;
        } else {
            // Bloqueio expirado, remove o arquivo
            unlink($blockFile);
        }
    }

    // Carrega ou cria dados do rate limit
    $rateLimitData = [];
    if (file_exists($rateLimitFile)) {
        $rateLimitData = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    }

    // Limpa dados antigos (mais de 2 minutos)
    $rateLimitData = array_filter($rateLimitData, function($entry) use ($currentMinute) {
        return ($currentMinute - $entry['minute']) <= 2;
    });

    // Conta requisições no minuto atual
    $requestsThisMinute = count(array_filter($rateLimitData, function($entry) use ($currentMinute) {
        return $entry['minute'] == $currentMinute;
    }));

    // Verifica se excedeu o limite
    if ($requestsThisMinute >= $maxRequests) {
        // Bloqueia o bot
        $blockData = [
            'blocked_at' => $currentTime,
            'blocked_until' => $currentTime + $blockDuration,
            'bot' => $detectedBot,
            'requests_count' => $requestsThisMinute,
            'last_ips' => array_unique(array_column($rateLimitData, 'ip')) // Últimos IPs usados
        ];

        file_put_contents($blockFile, json_encode($blockData));

        header("HTTP/1.1 429 Too Many Requests");
        header("Retry-After: $blockDuration");
        echo "429 Too Many Requests - Rate limit exceeded. Bot blocked for {$blockDuration} seconds.";

        // Log do bloqueio com informações dos IPs
        $ipsUsed = implode(', ', $blockData['last_ips']);
        error_log("Bot rate limit: {$detectedBot} blocked for exceeding {$maxRequests} requests/minute from IPs: {$ipsUsed}");

        exit;
    }

    // Adiciona a requisição atual com IP para auditoria
    $rateLimitData[] = [
        'minute' => $currentMinute,
        'ip' => $clientIP,
        'timestamp' => $currentTime
    ];

    // Salva os dados atualizados
    file_put_contents($rateLimitFile, json_encode($rateLimitData));
}

/**
 * Verifica se o bot está acessando fora do horário permitido.
 *
 * @param array $bots Lista de bots a serem monitorados.
 * @param int $startHour Hora inicial do acesso permitido (24h).
 * @param int $endHour Hora final do acesso permitido (24h).
 */
function bots_access($bots, $startHour, $endHour) {

    // Obtém a hora atual
    $currentHour = (int) date('H');

    // Obtém o agente de usuário do cliente
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Verifica se o agente corresponde a algum dos bots na lista
    foreach ($bots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            // Verifica se está fora do horário permitido
            if (!($currentHour >= $startHour && $currentHour < $endHour)){
                // Bloqueia o acesso com uma resposta 403 Forbidden
                header("HTTP/1.1 429 Too Many Requests");
                echo "429 Too Many Requests.";
                exit;
            }
        }
    }
}

// Lista de bots a serem monitorados
$botsFDP = [
    'Googlebot',
    'GoogleOther',
    'Bingbot',
    'GPTBot',
    // 'Presto',
    // 'petalbot',
    // 'amazonbot',
    // 'AhrefsBot',
    // 'SemrushBot',
    'meta-externalads',
    'meta-externalagent',
    'facebookexternalhit',
    // 'BacklinksExtendedBot',
    // 'Edg/114.0.1823.43',
    'postman',
    'AITrainingBot'
];

// 10 requisições por minuto, bloqueio de 2 minutos
bots_rate_limit($botsFDP, 30, 120);

// Define o intervalo de tempo permitido (exemplo: 20h às 6h)
$horaInicio = 22; // 20h (8 PM)
$horaFim = 6;     // 6h (6 AM)

// Chama a função para restringir o acesso
// bots_access($botsFDP, $horaInicio, $horaFim);
