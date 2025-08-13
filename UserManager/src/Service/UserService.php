<?php

namespace EditorIA2\UserManager\Service;

use EditorIA2\UserManager\Model\User;
use EditorIA2\UserManager\Repository\UserRepository;
use Exception;

class UserService
{
    private UserRepository $userRepository;
    private EmailService $emailService;

    public function __construct(UserRepository $userRepository, EmailService $emailService)
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    /**
     * @throws Exception
     */
    public function registerUser(string $name, string $email, string $cpfCnpj, string $password): User
    {
        // 1. Validação (básica por enquanto)
        if (empty($name) || empty($email) || empty($cpfCnpj) || empty($password)) {
            throw new Exception("Todos os campos são obrigatórios.", 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de e-mail inválido.", 400);
        }

        // 2. Verificar se o usuário já existe
        if ($this->userRepository->findByEmail($email)) {
            throw new Exception("Este e-mail já está em uso.", 409); // 409 Conflict
        }
        if ($this->userRepository->findByCpfCnpj($cpfCnpj)) {
            throw new Exception("Este CPF/CNPJ já está em uso.", 409);
        }

        // 3. Hash da senha
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        if ($passwordHash === false) {
            throw new Exception("Erro ao gerar hash da senha.", 500);
        }

        // 4. Gerar token de verificação
        $verificationToken = bin2hex(random_bytes(32));

        // 5. Criar e popular o modelo User
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->cpf_cnpj = $cpfCnpj;
        $user->password_hash = $passwordHash;
        $user->email_verification_token = $verificationToken;

        // 6. Salvar o usuário
        $success = $this->userRepository->create($user);
        if (!$success) {
            throw new Exception("Não foi possível registrar o usuário.", 500);
        }

        // 7. Enviar e-mail de verificação
        $verificationLink = $_ENV['APP_URL'] . '/verify-email.php?token=' . $verificationToken; // Exemplo de link
        $subject = 'Verifique seu endereço de e-mail';
        $body = "<p>Olá, {$user->name},</p>"
              . "<p>Obrigado por se registrar. Por favor, clique no link abaixo para verificar seu e-mail:</p>"
              . "<p><a href=\"{$verificationLink}\">{$verificationLink}</a></p>";

        $this->emailService->sendEmail($user->email, $user->name, $subject, $body);

        // 8. Retornar o usuário (sem o hash da senha)
        unset($user->password_hash);
        return $user;
    }

    /**
     * @throws Exception
     */
    public function verifyEmail(string $token): bool
    {
        if (empty($token)) {
            throw new Exception("O token de verificação é obrigatório.", 400);
        }

        $user = $this->userRepository->findByVerificationToken($token);

        if (!$user) {
            throw new Exception("Token de verificação inválido ou expirado.", 404);
        }

        if ($user->status === 'active') {
            throw new Exception("Este e-mail já foi verificado.", 409);
        }

        $user->status = 'active';
        $user->email_verification_token = null;

        return $this->userRepository->update($user);
    }
}