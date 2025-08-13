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

    /**
     * @throws Exception
     */
    public function forgotPassword(string $email): void
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de e-mail inválido.", 400);
        }

        $user = $this->userRepository->findByEmail($email);

        // Para evitar enumeração de usuários, não retornamos erro se o e-mail não for encontrado.
        // Apenas enviamos o e-mail se o usuário existir e estiver ativo.
        if ($user && $user->status === 'active') {
            // Gerar token e data de expiração
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = new \DateTimeImmutable('+1 hour');

            $user->password_reset_token = $resetToken;
            $user->password_reset_expires_at = $expiresAt->format('Y-m-d H:i:s');

            $this->userRepository->update($user);

            // Enviar e-mail de redefinição de senha
            $resetLink = $_ENV['APP_URL'] . '/reset-password.php?token=' . $resetToken;
            $subject = 'Redefinição de Senha';
            $body = "<p>Olá, {$user->name},</p>"
                  . "<p>Recebemos uma solicitação para redefinir sua senha. Se não foi você, por favor, ignore este e-mail.</p>"
                  . "<p>Para continuar, clique no link abaixo. Este link é válido por 1 hora:</p>"
                  . "<p><a href=\"{$resetLink}\">{$resetLink}</a></p>";

            $this->emailService->sendEmail($user->email, $user->name, $subject, $body);
        }
    }

    /**
     * @throws Exception
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        if (empty($token) || empty($newPassword)) {
            throw new Exception("O token e a nova senha são obrigatórios.", 400);
        }

        $user = $this->userRepository->findByPasswordResetToken($token);

        if (!$user) {
            throw new Exception("Token de redefinição de senha inválido.", 404);
        }

        // Verificar se o token expirou
        $expiresAt = new \DateTimeImmutable($user->password_reset_expires_at);
        if (new \DateTimeImmutable() > $expiresAt) {
            throw new Exception("Token de redefinição de senha expirado.", 400);
        }

        // Hash da nova senha
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        if ($passwordHash === false) {
            throw new Exception("Erro ao gerar hash da nova senha.", 500);
        }

        // Atualizar usuário
        $user->password_hash = $passwordHash;
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;

        return $this->userRepository->update($user);
    }
}