# 📚 Dev Learn API

Uma API RESTful para um sistema de gestão de cursos e progresso de aprendizado, construída sobre o framework **Laravel 12** e documentada com **OpenAPI (Swagger)**.

---

### 🚀 Tecnologias e Versões

| Badge                                                                                           | Descrição                                             |
| :---------------------------------------------------------------------------------------------- | :---------------------------------------------------- |
| [![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com/)               | Framework principal da aplicação.                     |
| [![PHP](https://img.shields.io/badge/PHP-8.4-777BB4)](https://www.php.net/)                     | Linguagem de programação (versão de desenvolvimento). |
| [![MySQL](https://img.shields.io/badge/Database-MySQL-00758F)](https://www.mysql.com/)          | Banco de dados relacional.                            |
| [![Composer](https://img.shields.io/badge/Composer-2.x-8A6BE4)](https://getcomposer.org/)       | Gerenciador de dependências.                          |
| [![Sanctum](https://img.shields.io/badge/Sanctum-4.x-FF2D20)](https://laravel.com/docs/sanctum) | Autenticação simples baseada em tokens (API).         |
| [![Swagger](https://img.shields.io/badge/Swagger-OpenAPI-85EA2D)](https://swagger.io/)          | Documentação interativa da API.                       |

---

## 🛠️ Requisitos de Instalação

Para rodar este projeto localmente, você precisa ter:

1. **Git**
2. **PHP** (mínimo 8.2, recomendado 8.4)
3. **Composer** (versão 2.x)
4. **Banco de dados** configurado (MySQL, PostgreSQL ou SQLite)

---

## ⚙️ Configuração e Instalação Local

Siga estes passos para configurar e executar o projeto em seu ambiente local:

### 1. Clonar o Repositório

```bash
git clone https://github.com/santyasm/dev-learn-api
cd dev-learn-api
```

### 2. Configurar Variáveis de Ambiente

Crie o arquivo .env a partir do exemplo fornecido:

```bash
cp .env.example .env
```

Edite o arquivo .env e configure as credenciais do seu banco de dados MySQL:
Ini, TOML

```TOML
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dev_learn_api
DB_USERNAME=seu_usuario_mysql
DB_PASSWORD=sua_senha_mysql
```

### 3. Instalar Dependências

Instale todas as dependências PHP necessárias usando o Composer:

```bash
composer install
```

### 4. Setup Inicial

Execute os comandos de configuração e migração:

```bash
# Roda as migrações (cria as tabelas no banco de dados configurado)

php artisan migrate --graceful

# (Opcional) Popule o banco com dados de teste

php artisan db:seed
```

### 5. Iniciar o Servidor

Inicie o servidor de desenvolvimento local do Laravel:

```bash
php artisan serve
```

## 🌐 Acesso à API e Documentação

O servidor da API está disponível em:  
**[https://dev-learn-api-main.laravel.cloud](https://dev-learn-api-main.laravel.cloud)**

### 🔹 Endpoints Principais

| Recurso / Descrição                       | Método    | URL                                                                | Acesso                              |
| ----------------------------------------- | --------- | ------------------------------------------------------------------ | ----------------------------------- |
| **API Base**                              | -         | [Link](https://dev-learn-api-main.laravel.cloud/api)               | Público                             |
| **Documentação (Swagger)**                | -         | [Link](https://dev-learn-api-main.laravel.cloud/api/documentation) | Público                             |
| **Registrar Usuário**                     | POST      | `/auth/register`                                                   | Público                             |
| **Login**                                 | POST      | `/auth/login`                                                      | Público                             |
| **Listar Cursos**                         | GET       | `/courses`                                                         | Público (opcionalmente autenticado) |
| **Criar Curso**                           | POST      | `/courses`                                                         | Admin                               |
| **Atualizar Curso**                       | PUT/PATCH | `/courses/{id}`                                                    | Admin                               |
| **Excluir Curso**                         | DELETE    | `/courses/{id}`                                                    | Admin                               |
| **Listar Vídeos**                         | GET       | `/videos`                                                          | Autenticado                         |
| **Detalhes do Vídeo**                     | GET       | `/videos/{id}`                                                     | Autenticado                         |
| **Criar Vídeo**                           | POST      | `/videos`                                                          | Admin                               |
| **Atualizar Vídeo**                       | PUT       | `/videos/{id}`                                                     | Admin                               |
| **Importar Vídeos de Playlist**           | POST      | `/videos/import`                                                   | Admin                               |
| **Excluir Vídeo**                         | DELETE    | `/videos/{id}`                                                     | Admin                               |
| **Listar Usuários**                       | GET       | `/users`                                                           | Admin                               |
| **Detalhes do Usuário**                   | GET       | `/users/{id}`                                                      | Admin                               |
| **Atualizar Usuário**                     | PUT/PATCH | `/users/{id}`                                                      | Admin                               |
| **Excluir Usuário**                       | DELETE    | `/users/{id}`                                                      | Admin                               |
| **Meus Dados**                            | GET       | `/user`                                                            | Autenticado                         |
| **Atualizar Meus Dados**                  | PUT/PATCH | `/user`                                                            | Autenticado                         |
| **Excluir Minha Conta**                   | DELETE    | `/user`                                                            | Autenticado                         |
| **Criar Matrícula**                       | POST      | `/enrollments`                                                     | Autenticado                         |
| **Atualizar Matrícula**                   | PUT/PATCH | `/enrollments/{id}`                                                | Autenticado                         |
| **Excluir Matrícula**                     | DELETE    | `/enrollments/{id}`                                                | Autenticado                         |
| **Meus Cursos Matriculados**              | GET       | `/user/enrollments`                                                | Autenticado                         |
| **Marcar Vídeo como Concluído**           | POST      | `/videos/{enrollment}/{video}/complete`                            | Autenticado                         |
| **Desmarcar Vídeo Concluído**             | DELETE    | `/videos/{enrollment}/{video}/complete`                            | Autenticado                         |
| **Listar Vídeos Concluídos da Matrícula** | GET       | `/enrollments/{enrollment}/completed-videos`                       | Autenticado                         |

> 🔗 Para mais detalhes sobre parâmetros, respostas e exemplos de uso, consulte a [documentação completa no Swagger](https://dev-learn-api-main.laravel.cloud/api/documentation).

---

### 💡 Dicas de Uso

-   Todas as rotas que exigem autenticação utilizam **Sanctum** com tokens.
-   Para obter um token válido, você pode usar os endpoints de **autenticação** disponíveis no Swagger:

    -   **Registrar usuário:** `POST /api/auth/register`
    -   **Login:** `POST /api/auth/login`

-   O token retornado deve ser enviado no header das requisições autenticadas
-   Rotas de administração requerem que o usuário tenha a role **admin**.

# 📄 Licença

Este projeto está sob a licença **MIT**.

Feito com 💜 por **Yasmin Santana**.
