# 🐳 Guia de Comandos Docker - Plataforma

## � **Informações do Documento**
- **Projeto:** CSI - Customer Success Intelligence
- **Versão:** 1.0
- **Data:** 29/09/2025
- **Autor(es):** Luan Felipe Tavares

## 📑 **Documentos Relacionados**
- [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)
- [Especificações Técnicas](./4%20Especificações%20Técnicas.md)

## �🚀 Comandos de Inicialização

### Primeira execução (build completo)
```bash
# Rebuild completo com limpeza total
./docker-rebuild.sh

# OU rebuild rápido (só do projeto)
./docker-quick-rebuild.sh

# OU manualmente
docker-compose down --volumes --remove-orphans
docker-compose build --no-cache
docker-compose up -d
```

## 🗄️ Comandos de Banco de Dados

### Verificar status do banco
```bash
./docker-db-status.sh
```

### Reset completo do banco
```bash
./docker-db-reset.sh
```

### Comandos manuais do banco
```bash
# Executar migrations
docker-compose exec backend php artisan migrate

# Executar seeders
docker-compose exec backend php artisan db:seed

# Reset completo (migrations + seeders)
docker-compose exec backend php artisan db:reset --force

# Forçar nova execução de seeders
docker-compose exec backend rm -f /var/www/html/storage/.seeded
docker-compose restart backend
```

## 📊 Monitoramento

### Ver logs
```bash
# Todos os serviços
docker-compose logs -f

# Serviço específico
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f postgres
```

### Status dos containers
```bash
docker-compose ps
```

### Entrar nos containers
```bash
# Backend (Laravel)
docker-compose exec backend bash

# Frontend (Node.js)
docker-compose exec frontend sh

# PostgreSQL
docker-compose exec postgres psql -U postgres -d plataforma
```

## 🔧 Comandos de Desenvolvimento

### Restart de serviços
```bash
# Todos
docker-compose restart

# Específico
docker-compose restart backend
docker-compose restart frontend
```

### Rebuild de um serviço específico
```bash
docker-compose build --no-cache backend
docker-compose up -d backend
```

### Limpar volumes (cuidado!)
```bash
docker-compose down --volumes
docker-compose up -d
```

## 🌐 URLs de Acesso

- **Frontend:** http://localhost:5173
- **Backend:** http://localhost:8000  
- **API:** http://localhost:8000/api/v1
- **Documentação API:** http://localhost:8000/api/documentation

## 🔍 Troubleshooting

### Seeders não executaram
```bash
# Verificar se o arquivo de controle existe
docker-compose exec backend ls -la /var/www/html/storage/.seeded

# Forçar execução
docker-compose exec backend rm -f /var/www/html/storage/.seeded
docker-compose restart backend
```

### Problemas de permissão
```bash
# Corrigir permissões
docker-compose exec backend chown -R www-data:www-data /var/www/html/storage
docker-compose exec backend chmod -R 775 /var/www/html/storage
```

### Container não sobe
```bash
# Ver logs de erro
docker-compose logs backend

# Rebuild forçado
docker-compose build --no-cache backend
docker-compose up -d backend
```

### Banco não conecta
```bash
# Verificar se PostgreSQL está rodando
docker-compose ps postgres

# Testar conexão
docker-compose exec postgres pg_isready -U postgres

# Ver logs do PostgreSQL
docker-compose logs postgres
```

## 📝 Arquivos Importantes

- `entrypoint.sh` - Script de inicialização do backend
- `docker-compose.yaml` - Configuração dos serviços
- `docker/Dockerfile.laravel` - Imagem do backend
- `docker/Dockerfile.node` - Imagem do frontend
- `storage/.seeded` - Arquivo de controle dos seeders

## ⚡ Comandos Rápidos

```bash
# Parar tudo
docker-compose down

# Subir tudo
docker-compose up -d

# Rebuild + subir
docker-compose up --build -d

# Ver status + logs
docker-compose ps && docker-compose logs --tail=20
```
