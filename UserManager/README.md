# Microserviço UserManager

Este é um microserviço PHP para gerenciamento de usuários, responsável por registro, verificação de e-mail e redefinição de senha.

## Configuração

1.  **Variáveis de Ambiente:**
    *   Copie o arquivo `.env.example` para um novo arquivo chamado `.env`.
    *   Preencha as variáveis de ambiente no arquivo `.env` com as configurações do seu banco de dados e servidor de e-mail (SMTP).

    ```
    DB_HOST=seu_host
    DB_PORT=sua_porta
    DB_DATABASE=seu_banco_de_dados
    DB_USERNAME=seu_usuario
    DB_PASSWORD=sua_senha

    SMTP_HOST=seu_servidor_smtp
    SMTP_PORT=sua_porta_smtp
    SMTP_USERNAME=seu_usuario_smtp
    SMTP_PASSWORD=sua_senha_smtp
    SMTP_FROM_EMAIL=seu_email_de_envio
    SMTP_FROM_NAME="Seu Nome"

    APP_URL=http://localhost/seu_projeto/UserManager/public
    ```

2.  **Dependências:**
    *   Navegue até o diretório `UserManager` e execute o Composer para instalar as dependências.
    ```bash
    composer install
    ```

## API Endpoints

Todos os endpoints esperam e retornam dados no formato JSON.

---

### 1. Registrar Novo Usuário

*   **Endpoint:** `POST /register`
*   **Descrição:** Registra um novo usuário no sistema e envia um e-mail de verificação.
*   **Corpo da Requisição (JSON):**
    ```json
    {
        "name": "Nome Completo do Usuário",
        "email": "usuario@example.com",
        "cpf_cnpj": "12345678901",
        "password": "senhaForte@123"
    }
    ```
*   **Resposta de Sucesso (201):**
    ```json
    {
        "status": "success",
        "message": "Usuário registrado com sucesso. Verifique seu e-mail para ativar a conta.",
        "user": {
            "name": "Nome Completo do Usuário",
            "email": "usuario@example.com",
            "cpf_cnpj": "12345678901"
        }
    }
    ```
*   **Respostas de Erro:**
    *   `400 Bad Request`: Campos ausentes, JSON inválido, ou dados de validação incorretos (e-mail, senha, CPF/CNPJ).
    *   `409 Conflict`: E-mail ou CPF/CNPJ já cadastrado.
    *   `500 Internal Server Error`: Erro ao salvar o usuário ou enviar o e-mail.

---

### 2. Verificar E-mail

*   **Endpoint:** `POST /verify-email`
*   **Descrição:** Ativa a conta de um usuário a partir do token enviado para o e-mail.
*   **Corpo da Requisição (JSON):**
    ```json
    {
        "token": "seu_token_de_verificacao"
    }
    ```
*   **Resposta de Sucesso (200):**
    ```json
    {
        "status": "success",
        "message": "E-mail verificado com sucesso."
    }
    ```
*   **Respostas de Erro:**
    *   `400 Bad Request`: Token ausente.
    *   `404 Not Found`: Token inválido ou expirado.
    *   `409 Conflict`: O e-mail já foi verificado.

---

### 3. Solicitar Redefinição de Senha

*   **Endpoint:** `POST /forgot-password`
*   **Descrição:** Inicia o processo de redefinição de senha. Envia um e-mail com um link para o usuário.
*   **Corpo da Requisição (JSON):**
    ```json
    {
        "email": "usuario@example.com"
    }
    ```
*   **Resposta de Sucesso (200):**
    ```json
    {
        "status": "success",
        "message": "Se um usuário com este e-mail existir, um link de redefinição de senha foi enviado."
    }
    ```
    *Nota: A resposta é sempre a mesma para evitar a enumeração de usuários.*

---

### 4. Redefinir a Senha

*   **Endpoint:** `POST /reset-password`
*   **Descrição:** Define uma nova senha para o usuário utilizando o token de redefinição.
*   **Corpo da Requisição (JSON):**
    ```json
    {
        "token": "seu_token_de_redefinicao",
        "password": "novaSenhaForte@456"
    }
    ```
*   **Resposta de Sucesso (200):**
    ```json
    {
        "status": "success",
        "message": "Senha redefinida com sucesso."
    }
    ```
*   **Respostas de Erro:**
    *   `400 Bad Request`: Token ou nova senha ausentes, ou a nova senha é fraca.
    *   `404 Not Found`: Token inválido.
    *   `400 Bad Request`: Token expirado.
