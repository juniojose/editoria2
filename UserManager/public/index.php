<?php

use EditorIA2\UserManager\Controller\UserController;

// Define o header para JSON
header("Content-Type: application/json; charset=UTF-8");

// Autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Analisa a requisição
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normaliza o caminho para remover a referência ao diretório do projeto se estiver rodando em um subdiretório
// Ex: /EditorIA2/UserManager/public/register -> /register
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($path, $scriptName) === 0) {
    $path = substr($path, strlen($scriptName));
}


// Função para enviar resposta JSON
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Roteamento
$routes = [
    'POST /register' => function () {
        $controller = new UserController();
        $controller->register();
    },
    'POST /verify-email' => function () {
        json_response(['status' => 'success', 'message' => 'Endpoint /verify-email atingido.']);
    },
    'POST /forgot-password' => function () {
        json_response(['status' => 'success', 'message' => 'Endpoint /forgot-password atingido.']);
    },
    'POST /reset-password' => function () {
        json_response(['status' => 'success', 'message' => 'Endpoint /reset-password atingido.']);
    },
];

$routeKey = "$method $path";

if (array_key_exists($routeKey, $routes)) {
    $routes[$routeKey]();
} else {
    json_response(['status' => 'error', 'message' => 'Endpoint não encontrado ou método não permitido.'], 404);
}