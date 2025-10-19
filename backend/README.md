# Laravel Repository Pattern API

## Visão Geral

Este projeto implementa uma API RESTful versionada usando Laravel, seguindo o Repository Pattern para separação de responsabilidades e facilitar a manutenção. A estrutura é projetada para ser escalável, testável e fácil de estender.

## Requisitos

 - PHP 8.4.5 ou superior
 - Composer 2.8.6 ou superior

## Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       └── V1/
│   │           └── UserController.php
│   ├── Resources/
│   │   └── V1/
│   │       └── UserResource.php
│   │   
│   └── Requests/
│       └── V1/
│           └── User/
│               ├── StoreUserRequest.php
│               └── UpdateUserRequest.php
├── Interfaces/
│   ├── RepositoryInterface.php
│   └── UserRepositoryInterface.php
├── Repositories/
│   ├── BaseRepository.php
│   └── UserRepository.php
├── Models/
│   └── User.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Características Principais

- **Repository Pattern**: Abstração da camada de dados para facilitar a manutenção e testes
- **API Versionada**: Suporte para múltiplas versões da API (v1, v2, etc.)
- **Form Requests**: Validação de dados encapsulada em classes específicas
- **API Resources**: Transformação de dados padronizada para respostas JSON
- **Injeção de Dependência**: Uso de interfaces para desacoplamento de componentes

## Componentes

### Interfaces

- **RepositoryInterface**: Define métodos básicos de CRUD para todos os repositórios
- **UserRepositoryInterface**: Estende a interface base com métodos específicos para usuários
- **ClientesRepositoryInterface**: Estende a interface base implementando um método de busca

### Repositórios

- **BaseRepository**: Implementação genérica dos métodos CRUD
- **UserRepository**: Implementação simples para o modelo User usando o BaseRepository
- **ClienteRepository**: Implementação avançada para o modelo Cliente usando o BaseRepository

### Controllers

- **V1/AuthController**: Implementação de autenticação JWT customizada utilizando um Web Service nativo do Senior para validar o usuário.

- **V1/UserController**: Exemplo de implementação básica de CRUD para usuários
- **V1/ClientesController**: Exemplo de implementação avançada de busca de clientes

### Resources

- **V1/UserResource**: Transformação básica de dados de usuário

### Form Requests

- **V1/StoreUserRequest e UpdateUserRequest**: Validação básica para criação/atualização

### Middleware

- **JwtAuthMiddleware**: Middleware para autenticação JWT customizado.
  - Disponibiliza as informações do usuário autenticado via `request()->auth`.

### Helpers

- **WsdlHelper**: Exemplo de helper para chamar serviços de WSDL. Converte os parâmetros de entrada em XML e envia para o WSDL, depois converte o XML de saída em objetos PHP.

## Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/seu-repositorio.git
   cd seu-repositorio
   ```

2. Instale as dependências:
   ```bash
   composer install
   ```

3. Configure o ambiente:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure o banco de dados no arquivo `.env`

5. Execute as migrações:
   ```bash
   php artisan migrate
   ```

6. (Opcional) Popule o banco com dados de teste:
   ```bash
   php artisan db:seed
   ```

## Uso

### Documentação - Swagger

A documentação da API está disponível em `http://localhost:8000/api/documentation`.

Todas as rotas da API estão documentadas, incluindo os parâmetros de busca e respostas.

Para acessar a documentação da API, execute o seguinte comando:

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
```

### Endpoints da API

#### V1

- `GET /api/v1/users` - Listar todos os usuários
- `GET /api/v1/users/{id}` - Obter um usuário específico
- `POST /api/v1/users` - Criar um novo usuário
- `PUT/PATCH /api/v1/users/{id}` - Atualizar um usuário
- `DELETE /api/v1/users/{id}` - Excluir um usuário

### Exemplos de Requisição

#### Criar um usuário (V1)

```bash
curl -X POST \
  http://localhost:8000/api/v1/users \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "active": true
}'
```

## Estendendo o Projeto

### Adicionar um Novo Modelo

1. Crie o modelo e migração:
   ```bash
   php artisan make:model Post -m
   ```

2. Crie a interface:
   ```php
   // app/Interfaces/PostRepositoryInterface.php
   namespace App\Interfaces;

   interface PostRepositoryInterface extends RepositoryInterface
   {
       // Métodos específicos para Post
   }
   ```

3. Crie o repositório:
   ```php
   // app/Repositories/PostRepository.php
   namespace App\Repositories;

   use App\Models\Post;
   use App\Interfaces\PostRepositoryInterface;

   class PostRepository extends BaseRepository implements PostRepositoryInterface
   {
       protected $modelClass = Post::class;
       
       // Implementações específicas
   }
   ```

4. Registre no service provider:
   ```php
   // app/Providers/RepositoryServiceProvider.php
   $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
   ```

5. Crie controllers, resources e requests para cada versão da API

### Adicionar uma Nova Versão da API

1. Crie novos controllers na pasta `app/Http/Controllers/API/V2/`
2. Crie novos resources na pasta `app/Http/Resources/V2/`
3. Crie novos requests na pasta `app/Http/Requests/V2/`
4. Adicione as rotas em `routes/api.php`:
   ```php
   Route::prefix('v2')->name('v2.')->group(function () {
       Route::apiResource('users', UserControllerV2::class);
       // Outras rotas
   });
   ```

## Testes

Execute os testes com PHPUnit:

```bash
php artisan test
```

Ou para um grupo específico:

```bash
php artisan test --group=repositories
```

## Contribuição

1. Faça o clone do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das alterações (`git commit -m 'Adiciona nova funcionalidade'`)
4. Envie para o branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request