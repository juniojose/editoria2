<?php

namespace EditorIA2\\UserManager\\Service;

use Exception;

class ValidationService
{
    /**
     * Higieniza uma string para evitar XSS.
     */
    public function sanitizeString(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida um endereço de e-mail.
     * @throws Exception se o e-mail for inválido.
     */
    public function validateEmail(?string $email): void
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de e-mail inválido.", 400);
        }
    }

    /**
     * Valida a força de uma senha.
     * @throws Exception se a senha for fraca.
     */
    public function validatePassword(?string $password): void
    {
        if (empty($password)) {
            throw new Exception("O campo senha é obrigatório.", 400);
        }
        
        // Mínimo de 8 caracteres
        if (strlen($password) < 8) {
            throw new Exception("A senha deve ter pelo menos 8 caracteres.", 400);
        }

        // Adicione outras regras se necessário (ex: letras maiúsculas, números, símbolos)
    }

    /**
     * Valida um CPF ou CNPJ.
     * @throws Exception se o documento for inválido.
     */
    public function validateCpfCnpj(?string $cpfCnpj): void
    {
        if (empty($cpfCnpj)) {
            throw new Exception("O campo CPF/CNPJ é obrigatório.", 400);
        }

        // Remove caracteres não numéricos
        $cleanedCpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);
        $length = strlen($cleanedCpfCnpj);

        if ($length !== 11 && $length !== 14) {
            throw new Exception("CPF/CNPJ deve ter 11 (CPF) ou 14 (CNPJ) dígitos.", 400);
        }

        // Lógica de validação de dígitos (simplificada por enquanto)
        // Uma implementação completa seria mais robusta.
        if (($length === 11 && !$this->isValidCpf($cleanedCpfCnpj)) || ($length === 14 && !$this->isValidCnpj($cleanedCpfCnpj))) {
             throw new Exception("CPF/CNPJ inválido.", 400);
        }
    }

    /**
     * Valida um CPF.
     */
    private function isValidCpf(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false; // CPF com todos os dígitos iguais
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida um CNPJ.
     */
    private function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false; // CNPJ com todos os dígitos iguais
        }

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++) {
                $d += $cnpj[$c] * $p;
                $p = ($p < 3) ? 9 : $p - 1;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}
