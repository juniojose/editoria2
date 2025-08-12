<?php

namespace EditorIA2\UserManager\Model;

class User
{
    public ?int $id;
    public string $name;
    public string $email;
    public string $cpf_cnpj;
    public string $password_hash;
    public string $role;
    public string $status;
    public ?string $email_verification_token;
    public ?string $password_reset_token;
    public ?string $password_reset_expires_at;
    public string $register_date;
    public string $last_update_date;
}
