<?php

namespace EditorIA2\UserManager\Repository;

use EditorIA2\UserManager\Model\User;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->hydrateUser($userData);
    }

    public function findByCpfCnpj(string $cpfCnpj): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE cpf_cnpj = ?");
        $stmt->execute([$cpfCnpj]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->hydrateUser($userData);
    }

    public function findByVerificationToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email_verification_token = ?");
        $stmt->execute([$token]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->hydrateUser($userData);
    }

    public function create(User $user): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (name, email, cpf_cnpj, password_hash, email_verification_token) 
             VALUES (?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $user->name,
            $user->email,
            $user->cpf_cnpj,
            $user->password_hash,
            $user->email_verification_token
        ]);
    }

    public function update(User $user): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET 
                name = :name,
                email = :email,
                cpf_cnpj = :cpf_cnpj,
                password_hash = :password_hash,
                role = :role,
                status = :status,
                email_verification_token = :email_verification_token,
                password_reset_token = :password_reset_token,
                password_reset_expires_at = :password_reset_expires_at
             WHERE id = :id"
        );

        return $stmt->execute([
            ':name' => $user->name,
            ':email' => $user->email,
            ':cpf_cnpj' => $user->cpf_cnpj,
            ':password_hash' => $user->password_hash,
            ':role' => $user->role,
            ':status' => $user->status,
            ':email_verification_token' => $user->email_verification_token,
            ':password_reset_token' => $user->password_reset_token,
            ':password_reset_expires_at' => $user->password_reset_expires_at,
            ':id' => $user->id,
        ]);
    }

    private function hydrateUser(array $data): User
    {
        $user = new User();
        $user->id = (int)$data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->cpf_cnpj = $data['cpf_cnpj'];
        $user->password_hash = $data['password_hash'];
        $user->role = $data['role'];
        $user->status = $data['status'];
        $user->email_verification_token = $data['email_verification_token'];
        $user->password_reset_token = $data['password_reset_token'];
        $user->password_reset_expires_at = $data['password_reset_expires_at'];
        $user->register_date = $data['register_date'];
        $user->last_update_date = $data['last_update_date'];

        return $user;
    }
}