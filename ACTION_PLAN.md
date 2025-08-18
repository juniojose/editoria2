# Plano de Implementação: Gerenciamento de API Keys

**Objetivo:** Permitir que usuários autenticados gerenciem suas próprias chaves de API para interagir com outros serviços.

---

### Fase 1: Preparação do Banco de Dados

1.  **Criar uma Tabela `api_keys`:** Adicionar uma nova migração de banco de dados para criar uma tabela `api_keys`.
    *   **Colunas:** `id`, `user_id` (chave estrangeira para `users.id`), `api_key` (única), `status` (`active`, `revoked`), `created_at`, `last_used_at`.
    *   Isso permite que um usuário tenha múltiplas chaves.

---

### Fase 2: Lógica no Backend (PHP)

1.  **Criar `ApiKeyRepository`:** Uma nova classe para interagir com a tabela `api_keys` (criar, buscar por chave, buscar por usuário, revogar).
2.  **Criar `ApiKeyService`:**
    *   `generateKeyForUser(int $userId)`: Gera uma nova chave, salva no banco e a retorna.
    *   `getKeysForUser(int $userId)`: Retorna todas as chaves de um usuário.
    *   `revokeKey(string $apiKey, int $userId)`: Revoga uma chave, garantindo que o usuário só possa revogar suas próprias chaves.
    *   `validateKey(string $apiKey)`: Verifica se uma chave é válida e ativa (será usada por outros microserviços para autenticação).
3.  **Criar `ApiKeyController`:**
    *   `POST /api/keys`: Endpoint protegido que chama `ApiKeyService->generateKeyForUser()`.
    *   `GET /api/keys`: Endpoint protegido que chama `ApiKeyService->getKeysForUser()`.
    *   `DELETE /api/keys/{apiKey}`: Endpoint protegido que chama `ApiKeyService->revokeKey()`.

---

### Fase 3: Autenticação baseada em Token

1.  **Atualizar o `UserController::login`:** Além de confirmar o login, deve gerar um token de sessão (JWT é o padrão moderno) que será usado para autenticar as requisições na API (como as do `ApiKeyController`).
2.  **Criar um Middleware de Autenticação:** Uma camada que intercepta as requisições para os endpoints protegidos, valida o token JWT e disponibiliza os dados do usuário (como o `user_id`) para os controllers.