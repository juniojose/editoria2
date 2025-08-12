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

2.  **Documentação:**
    *   Criar um `README.md` para o microserviço.

---

# Progresso da Implementação (Até 12/08/2025)

## Fase 1: CONCLUÍDA

*   **Estrutura de Diretórios:**
    *   O diretório `EditorIA2/UserManager/` foi criado.
    *   A estrutura interna (`public`, `src`, `config`, `database/migrations`) foi criada.
    *   A arquitetura de separação de responsabilidades foi definida em `src/` com os diretórios `Controller`, `Service`, `Repository` e `Model`.

*   **Configuração do Ambiente PHP:**
    *   `composer.json` foi inicializado e configurado.
    *   O autoloading PSR-4 para o namespace `EditorIA2\UserManager` foi configurado para apontar para `src/`.
    *   A dependência `vlucas/phpdotenv` foi adicionada e instalada, criando o diretório `vendor/`.

*   **Banco de Dados e Configuração:**
    *   O script de migração `database/migrations/001_create_users_table.sql` foi criado com a estrutura da tabela `users`.
    *   O arquivo de exemplo de variáveis de ambiente `.env.example` foi criado.
    *   O script de conexão com o banco de dados `config/database.php` foi implementado usando PDO e `dotenv`.

## Fase 2: EM ANDAMENTO

*   **Roteamento e Requisições:**
    *   O Front Controller `public/index.php` foi criado.
    *   Um roteador simples foi implementado para lidar com as rotas da API.

*   **Endpoint: `POST /register` (CONCLUÍDO):**
    *   `Model/User.php`: Criado para representar a entidade de usuário.
    *   `Repository/UserRepository.php`: Implementado com métodos `findByEmail`, `findByCpfCnpj`, e `create` (renomeado de `save`).
    *   `Service/UserService.php`: Criado com a lógica de negócio para registrar um novo usuário, incluindo validação, verificação de duplicados e hashing de senha.
    *   `Controller/UserController.php`: Implementado para lidar com a requisição HTTP, chamar o serviço e retornar a resposta JSON apropriada.
    *   A rota em `public/index.php` foi conectada ao `UserController`.

*   **Endpoint: `POST /verify-email` (INICIADO):**
    *   `Repository/UserRepository.php`: Atualizado com os métodos `findByVerificationToken` e `update`.
