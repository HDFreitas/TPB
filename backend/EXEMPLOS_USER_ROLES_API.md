# API de Gerenciamento de Roles de Usuários

## Visão Geral

Esta API permite gerenciar a vinculação entre usuários e roles utilizando o pacote Spatie Laravel Permission. Todas as operações respeitam o contexto de tenant, garantindo que usuários só possam gerenciar outros usuários do mesmo tenant.

## Endpoints Disponíveis

### 1. Atribuir Roles a um Usuário
**POST** `/api/v1/users/roles/assign`

Substitui todas as roles existentes do usuário pelas novas roles informadas.

**Exemplo de Request:**
```json
{
    "user_id": 1,
    "roles": ["Administrador", "Editor", "Viewer"]
}
```

**Exemplo de Response (200):**
```json
{
    "success": true,
    "message": "Roles atribuídas com sucesso ao usuário.",
    "data": {
        "user": {
            "id": 1,
            "name": "João Silva",
            "email": "joao@exemplo.com",
            "roles": [
                {
                    "id": 1,
                    "name": "admin",
                    "guard_name": "web"
                },
                {
                    "id": 2,
                    "name": "editor",
                    "guard_name": "web"
                }
            ]
        },
        "assigned_roles": ["Administrador", "Editor", "Viewer"],
        "message": "Roles atribuídas com sucesso ao usuário."
    }
}
```

### 2. Remover Roles de um Usuário
**POST** `/api/v1/users/roles/remove`

Remove roles específicas do usuário, mantendo as demais.

**Exemplo de Request:**
```json
{
    "user_id": 1,
    "roles": ["Editor"]
}
```

**Exemplo de Response (200):**
```json
{
    "success": true,
    "message": "Roles removidas com sucesso do usuário.",
    "data": {
        "user": {
            "id": 1,
            "name": "João Silva",
            "email": "joao@exemplo.com",
            "roles": [
                {
                    "id": 1,
                    "name": "admin",
                    "guard_name": "web"
                }
            ]
        },
        "removed_roles": ["Editor"],
        "message": "Roles removidas com sucesso do usuário."
    }
}
```

### 3. Obter Roles de um Usuário
**GET** `/api/v1/users/{userId}/roles`

Retorna todas as roles e permissões de um usuário específico.

**Exemplo de Response (200):**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "roles": ["admin", "editor"],
        "permissions": [
            "create-users",
            "edit-users",
            "delete-users",
            "create-posts",
            "edit-posts"
        ]
    }
}
```

### 4. Listar Usuários com suas Roles
**GET** `/api/v1/users/roles?per_page=15`

Lista todos os usuários do tenant com suas respectivas roles e permissões.

**Parâmetros de Query:**
- `per_page` (opcional): Número de itens por página (padrão: 15)

**Exemplo de Response (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "João Silva",
                "email": "joao@exemplo.com",
                "tenant_id": 1,
                "roles": [
                    {
                        "id": 1,
                        "name": "admin",
                        "guard_name": "web"
                    }
                ],
                "permissions": [
                    {
                        "id": 1,
                        "name": "create-users",
                        "guard_name": "web"
                    }
                ]
            }
        ],
        "first_page_url": "http://localhost/api/v1/users/roles?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost/api/v1/users/roles?page=1",
        "links": [...],
        "next_page_url": null,
        "path": "http://localhost/api/v1/users/roles",
        "per_page": 15,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

## Validações

### Validação de Request
- `user_id`: Obrigatório, deve ser um inteiro e o usuário deve existir no mesmo tenant
- `roles`: Obrigatório, deve ser um array com pelo menos uma role
- `roles.*`: Cada role deve ser uma string e deve existir na tabela de roles

### Segurança e Autorização
- **Middleware JWT**: Todas as rotas requerem autenticação via token JWT
- **Middleware Admin**: As rotas de atribuição/remoção de roles (`assign` e `remove`) requerem que o usuário tenha a role **"Administrador"**
- **Verificação de Tenant**: Todas as operações verificam se o usuário autenticado e o usuário alvo pertencem ao mesmo tenant
- **Logs de Auditoria**: Todas as operações de atribuição/remoção de roles são registradas em logs

### Níveis de Acesso
- **Rotas Protegidas por Role Administrador**:
  - `POST /api/v1/users/roles/assign` - Atribuir roles
  - `POST /api/v1/users/roles/remove` - Remover roles
  
- **Rotas de Consulta** (não requerem role Administrador):
  - `GET /api/v1/users/roles` - Listar usuários com roles
  - `GET /api/v1/users/{userId}/roles` - Obter roles de um usuário

## Códigos de Erro

- **200**: Operação realizada com sucesso
- **400**: Erro na requisição (ex: tentativa de modificar usuário de outro tenant)
- **401**: Não autorizado (token JWT inválido ou ausente)
- **403**: Acesso negado (usuário não possui role Administrador)
- **404**: Usuário não encontrado
- **422**: Erro de validação dos dados enviados

### Exemplo de Erro 403 (Acesso Negado):
```json
{
    "success": false,
    "message": "Acesso negado. Apenas usuários com role Administrador podem gerenciar roles de outros usuários.",
    "required_role": "Administrador",
    "user_roles": ["editor", "viewer"]
}
```

## Exemplos de Uso com cURL

### Atribuir roles:
```bash
curl -X POST "http://localhost/api/v1/users/roles/assign" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {seu-jwt-token}" \
  -d '{"user_id": 1, "roles": ["Administrador", "Editor"]}'
```

### Remover roles:
```bash
curl -X POST "http://localhost/api/v1/users/roles/remove" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {seu-jwt-token}" \
  -d '{"user_id": 1, "roles": ["Editor"]}'
```

### Obter roles do usuário:
```bash
curl -X GET "http://localhost/api/v1/users/1/roles" \
  -H "Authorization: Bearer {seu-jwt-token}"
```

### Listar usuários com roles:
```bash
curl -X GET "http://localhost/api/v1/users/roles?per_page=10" \
  -H "Authorization: Bearer {seu-jwt-token}"
```

## Integração com Frontend

Para usar no frontend, você pode criar um service que consome essas APIs:

```javascript
// Exemplo em JavaScript/Vue.js
const userRoleService = {
  async assignRoles(userId, roles) {
    const response = await fetch('/api/v1/users/roles/assign', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ user_id: userId, roles })
    });
    return response.json();
  },

  async removeRoles(userId, roles) {
    const response = await fetch('/api/v1/users/roles/remove', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ user_id: userId, roles })
    });
    return response.json();
  },

  async getUserRoles(userId) {
    const response = await fetch(`/api/v1/users/${userId}/roles`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.json();
  }
};
```
