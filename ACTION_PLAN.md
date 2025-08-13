# Plano de Ação: Criação do Microserviço `UserManager`

## Fase 1: Estruturação e Configuração do Projeto

1.  **Criação da Estrutura de Diretórios:**
    *   Criar o diretório raiz `EditorIA2/`.
    *   Dentro dele, criar o diretório do microserviço: `EditorIA2/UserManager`.
    *   Definir a estrutura interna do `UserManager` (`public`, `src`, `database/migrations`, `config`, `vendor`).

2.  **Configuração do Ambiente PHP:**
    *   Inicializar `composer.json`.
    *   Configurar o autoloading PSR-4.
    *   Adicionar dependências (`vlucas/phpdotenv`).

3.  **Banco de Dados e Configuração:**
    *   Criar script de migração para a tabela `users`.
    *   Criar arquivo `.env.example`.
    *   Criar script de conexão com o banco de dados em `config/database.php`.

## Fase 2: Implementação dos Endpoints da API

1.  **Roteamento e Requisições:**
    *   Implementar o Front Controller (`public/index.php`) com um roteador simples.

2.  **Endpoint: `POST /register`**
    *   Implementar a arquitetura completa: Controller, Service, Repository e Model.

3.  **Endpoint: `POST /verify-email`**
    *   Implementar a lógica para validar o token de e-mail e ativar o usuário.

4.  **Endpoint: `POST /reset-password`**
    *   Implementar a lógica de solicitação e execução de redefinição de senha.

## Fase 3: Finalização e Boas Práticas

1.  **Segurança e Validação:**
    *   Reforçar a validação de todas as entradas da API.
    *   Implementar tratamento de erros consistente.

2.  **Implementação do Serviço de Envio de E-mail:**
    *   Adicionar uma biblioteca de envio de e-mail (ex: PHPMailer ou Symfony Mailer).
    *   Criar uma classe de serviço (`EmailService`) para abstrair o envio dos e-mails de verificação e de redefinição de senha.
    *   Integrar o `EmailService` no `UserService`.

3.  **Documentação:**
    *   Criar um `README.md` para o microserviço.

---

# Progresso da Implementação (Até 13/08/2025)

## Fase 1: CONCLUÍDA

*   **Estrutura de Diretórios:** OK
*   **Configuração do Ambiente PHP:** OK
*   **Banco de Dados e Configuração:** OK

## Fase 2: EM ANDAMENTO

*   **Roteamento e Requisições:**
    *   O Front Controller `public/index.php` foi criado e a lógica de roteamento foi corrigida.

*   **Endpoint: `POST /register` (VALIDADO):**
    *   Arquitetura completa (Model, Repository, Service, Controller) implementada.
    *   **NOTA:** O envio de e-mail de verificação ainda não foi implementado (ver Fase 3).

*   **Endpoint: `POST /verify-email` (VALIDADO):**
    *   `Repository/UserRepository.php`: Atualizado com os métodos `findByVerificationToken` e `update`.
    *   `Service/UserService.php`: Atualizado com o método `verifyEmail`.
    *   `Controller/UserController.php`: Implementado para lidar com a requisição.
    *   A rota em `public/index.php` foi conectada e corrigida.
    *   **Testado e validado com sucesso em ambiente de servidor em 13/08/2025.**

*   **Endpoint: `POST /reset-password` (A FAZER):**
    *   A lógica de negócio e os endpoints (`/forgot-password` e `/reset-password`) precisam ser implementados.
