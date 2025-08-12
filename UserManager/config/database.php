<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Carrega as variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$dbConnection = $_ENV['DB_CONNECTION'] ?? 'mysql';
$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbPort = $_ENV['DB_PORT'] ?? '3306';
$dbDatabase = $_ENV['DB_DATABASE'] ?? '';
$dbUsername = $_ENV['DB_USERNAME'] ?? 'root';
$dbPassword = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "{$dbConnection}:host={$dbHost};port={$dbPort};dbname={$dbDatabase};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUsername, $dbPassword, $options);
} catch (\PDOException $e) {
    // Em um ambiente de produção, você não deveria expor a mensagem de erro.
    // Apenas para desenvolvimento:
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados.']);
    exit;
}

return $pdo;
