<?php

// Função para fazer a requisição POST para a API
function call_api(string $token): array
{
    // A URL da API é relativa ao diretório atual
    $apiUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/verify-email';

    $data = json_encode(['token' => $token]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'response' => json_decode($response, true)];
}

$pageTitle = "Verificação de E-mail";
$message = '';
$messageType = 'error'; // success or error

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $result = call_api($token);

    if ($result['code'] >= 200 && $result['code'] < 300) {
        $message = "Seu e-mail foi verificado com sucesso! Você já pode fechar esta página.";
        $messageType = 'success';
    } else {
        $message = $result['response']['message'] ?? 'Ocorreu um erro desconhecido ao verificar seu e-mail.';
        if ($result['code'] === 409) { // 409 Conflict - E-mail já verificado
             $message = "Este e-mail já foi verificado anteriormente.";
        }
    }
} else {
    $message = "Token de verificação não fornecido ou inválido.";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f9; color: #333; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .message { font-size: 1.1em; margin-bottom: 20px; }
        .message.success { color: #28a745; }
        .message.error { color: #dc3545; }
        .icon { font-size: 3em; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <?php echo ($messageType === 'success') ? '&#10004;' : '&#10006;'; ?>
        </div>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
</body>
</html>
