<?php

namespace Kmkz\UserManager\Controller;

use Kmkz\UserManager\Service\UserService;
use Exception;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            json_response(['status' => 'error', 'message' => 'JSON inválido.'], 400);
            return;
        }

        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (!$email || !$password) {
            json_response(['status' => 'error', 'message' => 'Campos obrigatórios ausentes: email, password.'], 400);
            return;
        }

        try {
            $user = $this->userService->loginUser($email, $password);

            // Iniciar sessão ou gerar token JWT aqui, se necessário.
            // Por enquanto, apenas retornamos os dados do usuário.

            json_response([
                'status' => 'success',
                'message' => 'Login bem-sucedido.',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'cpf_cnpj' => $user->cpf_cnpj
                ]
            ]);

        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 401;
            json_response(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function register()
    {
        $input = json_decode(file_get_contents('php://input'), true);

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
            $newUser = $this->userService->registerUser($name, $email, $cpfCnpj, $password);

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
            $this->userService->verifyEmail($token);
            json_response(['status' => 'success', 'message' => 'E-mail verificado com sucesso.']);
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            json_response(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function forgotPassword()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? null;

        if (!$email) {
            json_response(['status' => 'error', 'message' => 'O campo e-mail é obrigatório.'], 400);
            return;
        }

        try {
            $this->userService->forgotPassword($email);
            json_response(['status' => 'success', 'message' => 'Se um usuário com este e-mail existir, um link de redefinição de senha foi enviado.']);
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            json_response(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function resetPassword()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? null;
        $password = $input['password'] ?? null;

        if (!$token || !$password) {
            json_response(['status' => 'error', 'message' => 'O token e a nova senha são obrigatórios.'], 400);
            return;
        }

        try {
            $this->userService->resetPassword($token, $password);
            json_response(['status' => 'success', 'message' => 'Senha redefinida com sucesso.']);
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            json_response(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }
}
