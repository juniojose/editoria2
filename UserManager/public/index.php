<?php

// --- INÍCIO DO CÓDIGO DE DEBUG ---
// ATENÇÃO: REMOVA ISSO ANTES DE FINALIZAR
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIM DO CÓDIGO DE DEBUG ---

use EditorIA2\UserManager\Controller\UserController;

// Define o header para JSON
header("Content-Type: application/json; charset=UTF-8");

// Autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Analisa a requisição a partir do parâmetro 'route' fornecido pelo .htaccess
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Normaliza o caminho para garantir que ele comece com uma barra
$path = '/' . trim($route, '/');


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
    $routes[$routeKey]();
} else {
    json_response(['status' => 'error', 'message' => "Endpoint [{$path}] não encontrado ou método não permitido."], 404);
}