<?php

namespace EditorIA2\UserManager\Controller;

use EditorIA2\UserManager\Repository\UserRepository;
use EditorIA2\UserManager\Service\EmailService;
use EditorIA2\UserManager\Service\UserService;
use Exception;

class UserController
{
    public function register()
    {
        // a. Get raw POST data
        $input = json_decode(file_get_contents('php://input'), true);

        // c. Basic validation
        if (json_last_error() !== JSON_ERROR_NONE) {
            json_response(['status' => 'error', 'message' => 'JSON inválido.'], 400);
            return;
        }

        $name = $input['name'] ?? null;
        $email = $input['email'] ?? null;
        $cpfCnpj = $input['cpf_cnpj'] ?? null;
        $password = $input['password'] ?? null;

        if (!$name || !$email || !$cpfCnpj || !$password) {
            json_response(['status' => 'error', 'message' => 'Campos obrigatórios ausentes: name, email, cpf_cnpj, password.'], 400);
            return;
        }

        try {
            // d. Manual Dependency Injection
            $pdo = require __DIR__ . '/../../config/database.php';
            $userRepository = new UserRepository($pdo);
            $emailService = new EmailService();
            $userService = new UserService($userRepository, $emailService);

            // e. Call the service
            $newUser = $userService->registerUser($name, $email, $cpfCnpj, $password);

            // f. Success response
            json_response([
                'status' => 'success',
                'message' => 'Usuário registrado com sucesso. Verifique seu e-mail para ativar a conta.',
                'user' => [
                    'name' => $newUser->name,
                    'email' => $newUser->email,
                    'cpf_cnpj' => $newUser->cpf_cnpj
                ]
            ], 201);

        } catch (Exception $e) {
            // g. Error response
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            json_response(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function verifyEmail()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            json_response(['status' => 'error', 'message' => 'JSON inválido.'], 400);
            return;
        }

        $token = $input['token'] ?? null;

        if (!$token) {
            json_response(['status' => 'error', 'message' => 'O token é obrigatório.'], 400);
            return;
        }

        try {
            $pdo = require __DIR__ . '/../../config/database.php';
            $userRepository = new UserRepository($pdo);
            $emailService = new EmailService(); // Embora não usado diretamente por verifyEmail, é necessário para o construtor do UserService
            $userService = new UserService($userRepository, $emailService);

            $userService->verifyEmail($token);

            json_response(['status' => 'success', 'message' => 'E-mail verificado com sucesso.']);

        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            json_response(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }
}
