<?php

// UserManager/generate-token.php

// Script para gerar um novo token de aplicação e salvar no banco de dados de gerenciamento.

require_once __DIR__ . '/vendor/autoload.php';

// Carrega as variáveis de ambiente do .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --- Conexão com o Banco de Dados de Gerenciamento ---
function get_management_pdo(): PDO
{
    try {
        $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'] . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}

// --- Lógica do Script ---

if ($argc < 6) {
    echo "Uso: php generate-token.php <app_name> <db_host> <db_port> <db_database> <db_username> <db_password>\n";
    exit(1);
}

list(, $appName, $dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword) = $argv;

// Gerar um token de API seguro
$apiToken = bin2hex(random_bytes(32));

$pdo = get_management_pdo();

try {
    $stmt = $pdo->prepare(
        "INSERT INTO applications (app_name, api_token, db_host, db_port, db_database, db_username, db_password) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$appName, $apiToken, $dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword]);

    echo "Aplicação '$appName' criada com sucesso!\n";
    echo "API Token: $apiToken\n";

} catch (PDOException $e) {
    echo "Erro ao criar aplicação: " . $e->getMessage() . "\n";
    exit(1);
}
