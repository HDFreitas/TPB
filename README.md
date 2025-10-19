# CSI - Customer Success Intelligence

O **CSI (Customer Success Intelligence)** é uma plataforma multi-tenant desenvolvida para centralizar e gerenciar todas as interações com clientes, proporcionando insights inteligentes e KPIs personalizados para diferentes perfis de usuários.

## 🎯 **Objetivo do Projeto**

O CSI tem como principal objetivo ser uma **base de conhecimento inteligente** que:

- **Centraliza Interações**: Agrega dados de múltiplas fontes (ERP, CRM, sistemas de suporte)
- **Gera Insights**: Oferece dashboards e relatórios personalizados por perfil de usuário
- **Suporta Multi-tenancy**: Isolamento completo de dados entre diferentes organizações
- **Facilita Análise**: KPIs configuráveis e monitoramento em tempo real
- **Garante Segurança**: Sistema robusto de permissões baseado em RBAC

### **Funcionalidades Principais**

- ✅ **Gestão Multi-Tenant**: Suporte a múltiplas organizações com isolamento de dados
- ✅ **Sistema de Permissões**: Controle granular baseado em perfis (HUB, Administrador, Usuário)
- ✅ **Integrações Automatizadas**: Importação de dados de ERP Senior, Movidesk, RD Station
- ✅ **Dashboards Personalizados**: Visualizações específicas por perfil de usuário
- ✅ **Auditoria Completa**: Sistema de logs e métricas detalhados
- ✅ **Gestão de Clientes**: Perfis completos e histórico de interações

## 🏗️ **Arquitetura Técnica**

Esta aplicação utiliza uma arquitetura moderna e escalável:

- **Backend**: Laravel 12 com PHP 8.4, seguindo Repository Pattern
- **Frontend**: Vue.js 3 com TypeScript, Vite e Vuetify 3
- **Banco de Dados**: PostgreSQL com suporte multi-tenant
- **Hospedagem**: Microsoft Azure (App Service, WebJobs)
- **Containerização**: Docker Compose para desenvolvimento local

## 📚 **Documentação Completa**

O projeto conta com documentação técnica abrangente organizada na pasta `Documentacao/`:

### **📋 Documentos de Negócio**
- [**1. Documentação de Requisitos do CSI v1.0**](./Documentacao/1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md) - Requisitos funcionais e não-funcionais detalhados
- [**2. Documento de Arquitetura de Software**](./Documentacao/2%20Documento%20de%20Arquitetura%20de%20Software.md) - Visão arquitetural completa do sistema

### **📊 Documentos Técnicos**
- [**3. Diagramas Arquiteturais**](./Documentacao/3%20Diagramas%20Arquiteturais.md) - Diagramas C4, sequência e implantação
- [**4. Especificações Técnicas**](./Documentacao/4%20Especificações%20Técnicas.md) - Padrões de codificação e convenções
- [**5. Documentação da Estrutura**](./Documentacao/5%20Documentação%20da%20Estrutura.md) - Organização de pastas e rotas
- [**6. Sistema de Permissões**](./Documentacao/6%20Sistema%20de%20Permissões.md) - Implementação RBAC multi-tenant

### **🔧 Guias Operacionais**
- [**7. Guia de Comandos Docker**](./Documentacao/7%20Guia%20de%20Comandos%20Docker.md) - Comandos essenciais para desenvolvimento

## 🛠️ **Requisitos de Sistema**

- **Docker**: 27.3.1 ou superior
- **Docker Compose**: 2.30.3 ou superior
- **Node.js**: 18+ (para desenvolvimento local)
- **PHP**: 8.4+ (para desenvolvimento local)

## 📁 **Estrutura do Projeto**

```plaintext
├── README.md                          # Este arquivo
├── Documentacao/                      # 📚 Documentação completa
│   ├── 1 Documentação de Requisitos do CSI v1.0.md
│   ├── 2 Documento de Arquitetura de Software.md
│   ├── 3 Diagramas Arquiteturais.md
│   ├── 4 Especificações Técnicas.md
│   ├── 5 Documentação da Estrutura.md
│   ├── 6 Sistema de Permissões.md
│   └── 7 Guia de Comandos Docker.md
├── backend/                           # 🔧 API Laravel
├── frontend/                          # 🎨 Interface Vue.js
├── docker/                           # 🐳 Configurações Docker
├── docker-compose.yaml               # 🚀 Orquestração de serviços
└── .env.example                      # ⚙️ Variáveis de ambiente
```

## 🚀 **Como Executar o Projeto**

### **🔧 Configuração Inicial**

1. **Clone o Repositório**:
   ```bash
   git clone git@github.com:antoniv-informatica-ltda/plataforma.git
   cd plataforma
   ```

2. **Configure as Variáveis de Ambiente**:
   ```bash
   # Copie os arquivos de exemplo
   cp .env.example .env
   cp backend/.env.example backend/.env
   
   # Edite as variáveis conforme necessário
   ```

### **🐳 Execução com Docker (Recomendado)**

3. **Inicie o Ambiente Completo**:
   ```bash
   # Primeira execução (com build)
   docker-compose up --build
   
   # Execuções subsequentes
   docker-compose up -d
   ```

4. **Acesse a Aplicação**:
   - **Frontend**: [http://localhost:5173](http://localhost:5173)
   - **Backend API**: [http://localhost:8000](http://localhost:8000)
   
### **💻 Desenvolvimento Local (Opcional)**

Para desenvolvimento sem Docker:

```bash
# Backend - Laravel
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

# Frontend - Vue.js (em outro terminal)
cd frontend
npm install
npm run dev
```

### **🛠️ Comandos Úteis**

Para comandos específicos do Docker, consulte: [**Guia de Comandos Docker**](./Documentacao/7%20Guia%20de%20Comandos%20Docker.md)

```bash
# Ver logs
docker-compose logs -f

# Reset do banco de dados
docker-compose exec backend php artisan migrate:fresh --seed

# Executar testes
docker-compose exec backend php artisan test
docker-compose exec frontend npm run test

# Gerar documentação da API
docker-compose exec backend php artisan l5-swagger:generate
```


## 👥 **Perfis de Usuário**

O CSI suporta diferentes perfis com permissões específicas:

| Perfil | Descrição | Acesso |
|---------|-----------|---------|
| **HUB** | Administrador global | Todos os tenants, logs sistema, configurações globais |
| **Administrador** | Gestor do tenant | Usuários, permissões, KPIs do tenant |
| **Usuário** | Usuário final | Dashboards e relatórios liberados |

## 🔗 **Integrações Suportadas**

- **ERP Senior**: Dados de vendas e contratos
- **Movidesk**: Tickets e atendimentos de suporte  
- **RD Station**: Campanhas de marketing e conversões
- **CRM Eleve**: Oportunidades e leads

## 📊 **Tecnologias Utilizadas**

### **Backend**
- Laravel 12 + PHP 8.4
- PostgreSQL 15
- Redis (Cache)
- Spatie Permission (RBAC)
- JWT Authentication

### **Frontend** 
- Vue.js 3 + TypeScript
- Vite (Build Tool)
- Vuetify 3 (UI Framework)
- Pinia (State Management)

### **Infraestrutura**
- Docker + Docker Compose
- Microsoft Azure

## 🤝 **Contribuindo para o Projeto**

1. **Leia a Documentação**: Familiarize-se com os [**requisitos**](./Documentacao/1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md) e [**especificações técnicas**](./Documentacao/4%20Especificações%20Técnicas.md)

2. **Configure o Ambiente**: Siga as instruções de execução acima

3. **Siga os Padrões**: Consulte as [**especificações técnicas**](./Documentacao/4%20Especificações%20Técnicas.md) para convenções de código

4. **Crie uma Branch**: 
   ```bash
   git checkout -b feature/nova-funcionalidade
   ```

5. **Commit e Push**:
   ```bash
   git commit -m "feat: adiciona nova funcionalidade"
   git push origin feature/nova-funcionalidade
   ```

6. **Abra um Pull Request**: Descreva as alterações e referencie issues relacionadas

## 📞 **Suporte e Contato**

- **Repositório**: [GitHub - Plataforma CSI](https://github.com/antoniv-informatica-ltda/plataforma)
- **Documentação**: [Pasta Documentacao](./Documentacao/)
- **Issues**: Use o GitHub Issues para reportar bugs ou solicitar features

---

### 🎯 **Próximos Passos**

Para começar a usar ou contribuir com o CSI:

1. 📖 Leia a [**Documentação de Requisitos**](./Documentacao/1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
2. 🏗️ Entenda a [**Arquitetura do Sistema**](./Documentacao/2%20Documento%20de%20Arquitetura%20de%20Software.md)
3. 🚀 Execute o projeto seguindo as instruções acima
4. 🔧 Consulte o [**Guia de Comandos Docker**](./Documentacao/7%20Guia%20de%20Comandos%20Docker.md) conforme necessário# TPB
