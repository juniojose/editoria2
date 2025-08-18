<?php
// --- Início do Bloco de Diagnóstico ---
header("Content-Type: text/plain; charset=UTF-8");
echo "--- CONTEÚDO DO ARQUIVO NO SERVIDOR ---\\n\\n";
echo file_get_contents(__FILE__);
echo "\\n\\n--- FIM DO CONTEÚDO ---";
exit;
// --- Fim do Bloco de Diagnóstico ---

use Kmkz\UserManager\Controller\UserController;
use Kmkz\UserManager\Repository\UserRepository;
use Kmkz\UserManager\Service\EmailService;
use Kmkz\UserManager\Service\UserService;
use Kmkz\UserManager\Service\ValidationService;

// Define o header para JSON
header("Content-Type: application/json; charset=UTF-8");

// --- Função de Resposta JSON ---
function json_response($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// --- Autoloader e Variáveis de Ambiente ---
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// --- Middleware de Autenticação da Aplicação ---
$apiToken = $_SERVER['HTTP_X_API_TOKEN'] ?? null;

if (!$apiToken) {
    json_response(['status' => 'error', 'message' => 'Acesso não autorizado. O token da API não foi fornecido.'], 401);
}

// Conecta ao banco de dados de gerenciamento para validar o token
try {
    $mgmtDsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4";
    $mgmtPdo = new PDO($mgmtDsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $mgmtPdo->prepare("SELECT * FROM applications WHERE api_token = ? AND status = 'active'");
    $stmt->execute([$apiToken]);
    $application = $stmt->fetch();

    if (!$application) {
        json_response(['status' => 'error', 'message' => 'Acesso proibido. O token da API é inválido ou a aplicação está inativa.'], 403);
    }
} catch (PDOException $e) {
    json_response(['status' => 'error', 'message' => 'Erro interno do servidor.'], 500);
}

// --- Conexão com o Banco de Dados do Inquilino (Tenant) ---
try {
    $tenantDsn = "mysql:host={$application['db_host']};port={$application['db_port']};dbname={$application['db_database']};charset=utf8mb4";
    $tenantPdo = new PDO($tenantDsn, $application['db_username'], $application['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    json_response(['status' => 'error', 'message' => 'Não foi possível conectar ao banco de dados da aplicação.'], 500);
}

// --- Container de Injeção de Dependência Dinâmico (pós-autenticação) ---
$container = [];

$container[PDO::class] = $tenantPdo;
$container[EmailService::class] = new EmailService();
$container[ValidationService::class] = new ValidationService();
$container[UserRepository::class] = new UserRepository($container[PDO::class]);
$container[UserService::class] = new UserService(
    $container[UserRepository::class],
    $container[EmailService::class],
    $container[ValidationService::class]
);
$container[UserController::class] = new UserController($container[UserService::class]);

// --- Roteamento ---
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';
$prefix = 'UserManager/public/';
if (strpos($route, $prefix) === 0) {
    $route = substr($route, strlen($prefix));
}
$path = '/' . trim($route, '/');

$routes = [
    'POST /login' => [UserController::class, 'login'],
    'POST /register' => [UserController::class, 'register'],
    'POST /verify-email' => [UserController::class, 'verifyEmail'],
    'POST /forgot-password' => [UserController::class, 'forgotPassword'],
    'POST /reset-password' => [UserController::class, 'resetPassword'],
];

if ($path === '/' || $path === '') {
    if ($method === 'GET') {
        json_response(['status' => 'success', 'message' => "API do UserManager está online para a aplicação: {$application['app_name']}."], 200);
    }
    json_response(['status' => 'error', 'message' => 'Método não permitido para a raiz.'], 405);
}

$routeKey = "$method $path";

if (array_key_exists($routeKey, $routes)) {
    list($controllerClass, $methodName) = $routes[$routeKey];
    
    // Resolve o controller diretamente do container já montado
    $controller = $container[$controllerClass];
    
    // Chama o método
    $controller->$methodName();
} else {
    json_response(['status' => 'error', 'message' => "Endpoint [{$path}] não encontrado ou método não permitido."], 404);
}
