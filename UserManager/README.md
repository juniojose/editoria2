# Microserviço UserManager

*Este projeto é mantido pela Kmkz.*

Este é um microserviço PHP para gerenciamento de usuários, responsável por registro, verificação de e-mail e redefinição de senha. O serviço opera em um modelo **multi-inquilino**, onde cada aplicação cliente possui seu próprio banco de dados de usuários.

## Autenticação

Todas as requisições para a API devem incluir um token de autenticação de aplicação no cabeçalho HTTP.

*   **Cabeçalho:** `X-API-Token`
*   **Valor:** O token de API fornecido para a sua aplicação.

Requisições sem um token válido resultarão em uma resposta `401 Unauthorized` ou `403 Forbidden`.

## Configuração

1.  **Banco de Dados de Gerenciamento:**
    *   O arquivo `.env` na raiz do `UserManager` deve conter as credenciais para o **banco de dados de gerenciamento**. Este banco de dados contém a tabela `applications`.

2.  **Estrutura do Banco de Dados (Migrations):**
    *   Antes de usar o serviço, você precisa garantir que as tabelas existam.
    *   **Tabela de Aplicações:** Execute o script `database/migrations/002_create_applications_table.sql` no seu banco de dados de gerenciamento.
    *   **Tabela de Usuários:** Para cada banco de dados de inquilino, execute o script `database/migrations/001_create_users_table.sql`.

3.  **Provisionamento de Inquilinos (Aplicações):**
    *   Para registrar uma nova aplicação cliente, use o script `generate-token.php` na linha de comando. Lembre-se de envolver senhas com caracteres especiais em aspas simples.
    *   **Exemplo:**
    ```bash
    php generate-token.php nome_da_app localhost 3306 banco_de_dados_do_inquilino usuario_do_bd 'senha_do_bd!@#'
    ```
    *   Este comando irá registrar a aplicação no banco de dados de gerenciamento e retornar o `API Token` que deverá ser usado no cabeçalho `X-API-Token`.

4.  **Dependências:**
    *   Execute o Composer para instalar as dependências.
    ```bash
    composer install
    ```

## API Endpoints

Todos os endpoints esperam e retornam dados no formato JSON.

---

### 1. Registrar Novo Usuário

*   **Endpoint:** `POST /register`
*   **Descrição:** Registra um novo usuário no banco de dados do inquilino e envia um e-mail de verificação.
*   **Cabeçalhos (Headers):**
    *   `X-API-Token`: `seu_token_de_api`
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
    *   `401/403`: Token de API ausente ou inválido.
    *   `400 Bad Request`: Campos ausentes, JSON inválido, ou dados de validação incorretos.
    *   `409 Conflict`: E-mail ou CPF/CNPJ já cadastrado.

---

### 2. Verificar E-mail

*   **Endpoint:** `POST /verify-email`
*   **Descrição:** Ativa a conta de um usuário a partir do token enviado para o e-mail.
*   **Cabeçalhos (Headers):**
    *   `X-API-Token`: `seu_token_de_api`
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
    *   `401/403`: Token de API ausente ou inválido.
    *   `400 Bad Request`: Token ausente.
    *   `404 Not Found`: Token de verificação inválido ou expirado.

---

### 3. Solicitar Redefinição de Senha

*   **Endpoint:** `POST /forgot-password`
*   **Descrição:** Inicia o processo de redefinição de senha.
*   **Cabeçalhos (Headers):**
    *   `X-API-Token`: `seu_token_de_api`
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

---

### 4. Redefinir a Senha

*   **Endpoint:** `POST /reset-password`
*   **Descrição:** Define uma nova senha para o usuário.
*   **Cabeçalhos (Headers):**
    *   `X-API-Token`: `seu_token_de_api`
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
    *   `401/403`: Token de API ausente ou inválido.
    *   `400 Bad Request`: Token ou nova senha ausentes, ou a nova senha é fraca.
    *   `404 Not Found`: Token de redefinição inválido.