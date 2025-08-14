<?php

use EditorIA2\UserManager\Controller\UserController;
use EditorIA2\UserManager\Repository\UserRepository;
use EditorIA2\UserManager\Service\EmailService;
use EditorIA2\UserManager\Service\UserService;
use EditorIA2\UserManager\Service\ValidationService;

// Define o header para JSON
header("Content-Type: application/json; charset=UTF-8");

// Autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Analisa a requisição a partir do parâmetro 'route' fornecido pelo .htaccess
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Remove o prefixo do caminho se ele existir, para ajustar o roteamento
$prefix = 'UserManager/public/';
if (strpos($route, $prefix) === 0) {
    $route = substr($route, strlen($prefix));
}

// Normaliza o caminho para garantir que ele comece com uma barra
$path = '/' . trim($route, '/');

// Função para enviar resposta JSON
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// --- Container de Injeção de Dependência Simples ---
$dependencies = [];

$dependencies['pdo'] = function () {
    return require __DIR__ . '/../config/database.php';
};

$dependencies[UserRepository::class] = function ($c) {
    return new UserRepository($c['pdo']());
};

$dependencies[EmailService::class] = function () {
    return new EmailService();
};

$dependencies[ValidationService::class] = function () {
    return new ValidationService();
};

$dependencies[UserService::class] = function ($c) {
    return new UserService(
        $c[UserRepository::class]($c),
        $c[EmailService::class]($c),
        $c[ValidationService::class]($c)
    );
};

$dependencies[UserController::class] = function ($c) {
    return new UserController($c[UserService::class]($c));
};

// --- Fim do Container ---

// Roteamento
$routes = [
    'POST /register' => [UserController::class, 'register'],
    'POST /verify-email' => [UserController::class, 'verifyEmail'],
    'POST /forgot-password' => [UserController::class, 'forgotPassword'],
    'POST /reset-password' => [UserController::class, 'resetPassword'],
];

// Adiciona uma rota para a raiz, se necessário
if ($path === '/' || $path === '') {
    if ($method === 'GET') {
        json_response(['status' => 'success', 'message' => 'API do UserManager está online.']);
    } else {
        json_response(['status' => 'error', 'message' => 'Método não permitido para a raiz.'], 405);
    }
    exit;
}

$routeKey = "$method $path";

if (array_key_exists($routeKey, $routes)) {
    list($controllerClass, $methodName) = $routes[$routeKey];
    
    // Resolve o controller a partir do container
    $controller = $dependencies[$controllerClass]($dependencies);
    
    // Chama o método
    $controller->$methodName();
} else {
    json_response(['status' => 'error', 'message' => "Endpoint [{$path}] não encontrado ou método não permitido."], 404);
}
