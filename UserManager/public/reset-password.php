<?php

$pageTitle = "Redefinição de Senha";
$token = $_GET['token'] ?? '';
$message = '';
$messageType = 'error'; // success or error

// Função para chamar a API de redefinição
function call_reset_api(string $token, string $password): array
{
    $apiUrl = 'https' . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset-password';
    $data = json_encode(['token' => $token, 'password' => $password]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($data)]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'response' => json_decode($response, true)];
}

// Lógica do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (empty($token) || empty($password) || empty($passwordConfirm)) {
        $message = 'Todos os campos são obrigatórios.';
    } elseif ($password !== $passwordConfirm) {
        $message = 'As senhas não coincidem.';
    } else {
        $result = call_reset_api($token, $password);
        if ($result['code'] >= 200 && $result['code'] < 300) {
            $message = 'Sua senha foi redefinida com sucesso!';
            $messageType = 'success';
        } else {
            $message = $result['response']['message'] ?? 'Ocorreu um erro desconhecido.';
        }
    }
}

if (empty($token)) {
    $message = "Token de redefinição não fornecido ou inválido.";
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
        .container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px; }
        .message { font-size: 1.1em; margin-bottom: 20px; min-height: 25px; }
        .message.success { color: #28a745; }
        .message.error { color: #dc3545; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { background-color: #007bff; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1em; }
        .btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <?php if ($messageType === 'success'): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>
            <div id="message-box" class="message error"><?php echo htmlspecialchars($message); ?></div>
            <form action="" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="password">Nova Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmar Nova Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn">Redefinir Senha</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
