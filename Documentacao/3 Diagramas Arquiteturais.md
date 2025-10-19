##### **Informações do Documento**
- **Projeto:** CSI - Customer Success Intelligence
- **Versão:** 2.0
- **Data:** 29/09/2025
- **Autor(es):** Luan Felipe Tavares
- **Ferramenta de Diagramação:** Mermaid

### **Documentos Relacionados**
- [Documentação de Requisitos do CSI v1.0](./1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
- [Documento de Arquitetura de Software](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Especificações Técnicas](./4%20Especificações%20Técnicas.md)
- [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)
- [Sistema de Permissões](./6%20Sistema%20de%20Permissões.md)

### **Histórico de Revisões**
| Versão | Data | Autor | Descrição das Alterações |
|--------|------|-------|-----------------------|
| 1.0 | 15/07/2025 | Luan Tavares | Versão inicial dos diagramas |
| 1.1 | 15/07/2025 | Luan Tavares | Adicionados diagramas C4, sequência e implantação |
| 2.0 | 29/09/2025 | Luan Tavares | Alinhamento com requisitos v1.0 e DAS v2.0:<br/>- Incluídos KPIs no MVP<br/>- Adicionado Power BI<br/>- Removido Redis (ADR-006)<br/>- Movidas integrações CRM/RD para V2<br/>- Adicionados diagramas de KPI |iagramas Arquiteturais**

### **Informações do Documento**
- **Projeto:** CSI - Customer Success Intelligence
- **Versão:** 1.1
- **Data:** 15/07/2025
- **Autor(es):** Luan Felipe Tavares
- **Ferramenta de Diagramação:** Mermaid

### **Documentos Relacionados**
- [Documentação de Requisitos do CSI v1.0](./1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
- [Documento de Arquitetura de Software](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Especificações Técnicas](./4%20Especificações%20Técnicas.md)

### **Histórico de Revisões**
| Versão | Data | Autor | Descrição das Alterações |
|--------|------|-------|-------------------------|
| 1.0 | 15/07/2025 | Luan Tavares | Versão inicial dos diagramas |
| 1.1 | 15/07/2025 | Luan Tavares | Adicionados diagramas C4, sequência e implantação |

---

## **1. Introdução**

### **1.1 Objetivo**
Este documento apresenta os diagramas arquiteturais do sistema CSI (Customer Success Intelligence), fornecendo diferentes visões e níveis de abstração para compreensão completa da arquitetura multi-tenant baseada em Laravel/Vue.js hospedada no Azure, incluindo o sistema de KPIs configuráveis e integrações com Power BI.

### **1.2 Notações e Convenções**
| Elemento | Representação | Descrição |
|----------|---------------|-----------|
| Sistema CSI | Retângulo azul | Sistema principal sendo documentado |
| Sistemas MVP | Retângulo verde | APIs e sistemas incluídos no MVP |
| Sistemas V2 | Retângulo cinza tracejado| APIs e sistemas para versão futura |
| Usuários | Ícone de pessoa | Atores do sistema (HUB, Admin, Usuário) |
| Banco de Dados | Cilindro | PostgreSQL (sem cache Redis) |
| Componentes | Retângulo verde | Módulos internos do Laravel/Vue |
| Azure Services | Retângulo roxo | Serviços da Microsoft Azure |
| Power BI | Retângulo amarelo | Fonte de indicadores pré-processados |

---

## **2. Diagrama de Contexto (C4 - Nível 1)**

### **2.1 Descrição**
Visão de mais alto nível mostrando como o sistema CSI se relaciona com usuários e sistemas externos no contexto organizacional.

### **2.2 Diagrama**

```mermaid
C4Context
    title Sistema CSI - Diagrama de Contexto

    Person(hub, "Usuário HUB", "Gerencia tenants e monitora logs globalmente. Perfil técnico alto.")
    Person(admin, "Administrador", "Gerencia KPIs e usuários do tenant. Perfil técnico médio.")
    Person(user, "Usuário Final", "Visualiza dashboards e KPIs. Perfil técnico baixo.")

    System(csi, "CSI System", "Customer Success Intelligence - Sistema multi-tenant para gestão de KPIs configur\u00e1veis e interações com clientes")

    System_Ext(erp, "ERP Senior", "Sistema de gestão empresarial - dados de vendas e contratos")
    System_Ext(movidesk, "Movidesk", "Sistema de suporte - tickets e atendimentos")
    System_Ext(powerbi, "Power BI", "Plataforma de BI - indicadores pré-processados")
    
    System_Ext(crm_eleve, "CRM Eleve", "Sistema de relacionamento - oportunidades e leads [V2]")
    System_Ext(rdstation, "RD Station", "Marketing automation - campanhas e conversões [V2]")

    BiRel(hub, csi, "Gerencia tenants, monitora logs, configura sistema")
    BiRel(admin, csi, "Configura KPIs, gerencia usuários e contatos")
    BiRel(user, csi, "Visualiza dashboards KPIs e indicadores BI")

    Rel(csi, erp, "Importa interações, sincroniza contatos", "REST/SOAP")
    Rel(csi, movidesk, "Importa tickets, sincroniza contatos", "REST API")
    Rel(csi, powerbi, "Consulta indicadores", "REST API")
    
    Rel(csi, crm_eleve, "Futuro: Importa oportunidades", "REST API")
    Rel(csi, rdstation, "Futuro: Importa campanhas", "REST API")

    UpdateLayoutConfig($c4ShapeInRow="3", $c4BoundaryInRow="1")
```

### **2.3 Elementos do Diagrama**

| Elemento | Tipo | Descrição | Tecnologia | Escopo |
|----------|------|-----------|------------|---------|
| Usuário HUB | Ator | Administrador global do sistema | Interface Web | MVP |
| Administrador | Ator | Administrador do tenant | Interface Web | MVP |
| Usuário Final | Ator | Usuário que visualiza KPIs | Interface Web | MVP |
| CSI System | Sistema | Sistema principal multi-tenant | Laravel + Vue.js | MVP |
| ERP Senior | Sistema Externo MVP | Sistema de gestão empresarial | REST/SOAP API | MVP |
| Movidesk | Sistema Externo MVP | Sistema de suporte | REST API | MVP |
| Power BI | Sistema Externo MVP | Plataforma de BI | REST API | MVP |
| CRM Eleve | Sistema Externo V2 | CRM para oportunidades | REST API | V2 |
| RD Station | Sistema Externo V2 | Automação de marketing | REST API | V2 |

### **2.4 Integrações por Escopo**

**Integrações do MVP:**
| Sistema Externo | Protocolo | Tipo de Integração | Criticidade | Funcionalidade |
|-----------------|-----------|-------------------|-------------|----------------|
| ERP Senior | REST/SOAP | Assíncrona (Jobs) | Alta | Importação de interações e clientes |
| Movidesk | REST | Assíncrona (Jobs) | Média | Importação de tickets de suporte |
| Power BI | REST | Síncrona | Média | Consulta de indicadores pré-processados |

**Integrações Futuras (V2):**
| Sistema Externo | Protocolo | Tipo de Integração | Criticidade | Funcionalidade |
|-----------------|-----------|-------------------|-------------|----------------|
| CRM Eleve | REST | Assíncrona (Jobs) | Alta | Importação de oportunidades e leads |
| RD Station | REST | Assíncrona (Jobs) | Média | Importação de campanhas de marketing |

**Funcionalidades de Sincronização (MVP):**
- **Contatos**: Sincronização bidirecional com ERP Senior e Movidesk
- **KPIs**: Cálculo automático baseado em interações importadas
- **Indicadores**: Consulta em tempo real ao Power BI

---

## **3. Diagrama de Componentes (C4 - Nível 2)**

### **3.1 Descrição**
Decompõe o sistema CSI em seus componentes internos e suas interações, mostrando a arquitetura Laravel/Vue.js.

### **3.2 Diagrama**

```mermaid
C4Container
    title CSI System - Diagrama de Componentes (MVP)

    Person(user, "Usuário", "HUB, Admin ou Usuário Final")

    Container_Boundary(csi, "CSI System") {
        Container(frontend, "Frontend Vue.js", "Vue 3, TypeScript, Vite", "Interface responsiva para gestão de KPIs e dashboards")
        Container(api, "Backend Laravel", "Laravel 12, PHP 8.4", "API REST para lógica de negócio e integrações")
        Container(queue, "Queue Workers", "Laravel Jobs + WebJobs", "Processamento assíncrono de integrações e cálculos de KPIs")
        ContainerDb(database, "PostgreSQL", "Azure Database", "Armazenamento multi-tenant de dados")
        Container(storage, "Azure Blob Storage", "Files & Logs", "Armazenamento de arquivos e logs de auditoria")
        Container(kpi_engine, "KPI Engine", "Laravel Services", "Cálculo automático de KPIs e métricas")
    }

    System_Ext(erp, "ERP Senior", "Sistema empresarial [MVP]")
    System_Ext(support, "Movidesk", "Sistema de suporte [MVP]")
    System_Ext(powerbi, "Power BI", "Business Intelligence [MVP]")
    
    System_Ext(crm_eleve, "CRM Eleve", "CRM [V2 - Futuro]", $tags="future")
    System_Ext(rd_station, "RD Station", "Marketing [V2 - Futuro]", $tags="future")

    Rel(user, frontend, "Acessa via browser", "HTTPS")
    Rel(frontend, api, "Consume API", "JSON/HTTPS")
    BiRel(api, database, "Lê/Escreve dados", "SQL")
    Rel(api, storage, "Logs e arquivos", "HTTPS")
    Rel(api, queue, "Dispara jobs", "In-process")
    Rel(api, kpi_engine, "Solicita cálculos", "In-process")
    
    Rel(queue, erp, "Integração ERP [MVP]", "REST/SOAP")
    Rel(queue, support, "Integração Suporte [MVP]", "REST")
    Rel(api, powerbi, "Consulta indicadores [MVP]", "REST")
    
    Rel(queue, crm_eleve, "Futuro: Integração CRM", "REST", $tags="future")
    Rel(queue, rd_station, "Futuro: Integração Marketing", "REST", $tags="future")
    
    BiRel(queue, database, "Processa dados", "SQL")
    BiRel(kpi_engine, database, "Calcula KPIs", "SQL")
```

### **3.3 Componentes Principais (MVP)**

| Componente | Responsabilidade | Tecnologia | Dependências | Escopo |
|------------|------------------|------------|--------------|---------|
| Frontend Vue.js | Interface do usuário, dashboards | Vue 3, TypeScript, Vite | Backend API | MVP |
| Backend Laravel | API REST, autenticação, RBAC | Laravel 12, PHP 8.4 | Database, Storage | MVP |
| Queue Workers | Integrações assíncronas | Laravel Jobs + Azure WebJobs | Database, APIs Externas | MVP |
| KPI Engine | Cálculo automático de KPIs | Laravel Services | Database | MVP |
| PostgreSQL | Persistência multi-tenant | Azure Database for PostgreSQL | - | MVP |
| Azure Blob Storage | Arquivos e logs de auditoria | Azure Storage | - | MVP |

**Nota sobre Redis**: Conforme definido na ADR-006, o sistema **não utiliza Redis** para simplificar a arquitetura e reduzir a complexidade operacional.

### **3.4 Interfaces entre Componentes (MVP)**

| Interface | De | Para | Protocolo | Formato | Tipo |
|-----------|-----|------|-----------|---------|------|
| REST API | Frontend | Backend | HTTPS | JSON | Síncrona |
| Database Connection | Backend | PostgreSQL | TCP | SQL | Síncrona |
| Storage API | Backend | Blob Storage | HTTPS | Binary | Síncrona |
| Job Queue | Backend | Queue Workers | In-process | Objects | Assíncrona |
| KPI Calculation | Backend | KPI Engine | In-process | Objects | Síncrona |
| ERP Integration | Queue Workers | ERP Senior | HTTPS | REST/SOAP | Assíncrona |
| Support Integration | Queue Workers | Movidesk | HTTPS | JSON | Assíncrona |
| BI Integration | Backend | Power BI | HTTPS | JSON | Síncrona |

---

## **4. Diagrama de Implantação**

### **4.1 Descrição**
Mostra como o sistema CSI é implantado na infraestrutura Microsoft Azure.

### **4.2 Diagrama - Ambiente de Produção**

```mermaid
C4Deployment
    title CSI System - Diagrama de Implantação Azure (MVP)

    Deployment_Node(client, "Dispositivos dos Usuários", "Windows, macOS, Mobile") {
        Container(browser, "Web Browser", "Chrome, Firefox, Safari, Edge", "Interface do usuário CSI")
    }

    Deployment_Node(azure, "Microsoft Azure", "Cloud Platform") {
        Deployment_Node(appservice, "Azure App Service", "PaaS Web Hosting") {
            Container(webapp, "CSI Web App", "Laravel 12 + Vue.js", "Aplicação principal do CSI com KPI Engine")
            Container(worker, "WebJobs", "Laravel Queue Workers", "Processamento assíncrono de integrações")
        }

        Deployment_Node(database, "Azure Database", "PostgreSQL PaaS") {
            ContainerDb(postgres, "PostgreSQL 15", "Multi-tenant Database", "Dados do sistema CSI + KPIs calculados")
        }

        Deployment_Node(storage_node, "Azure Storage", "Blob Storage") {
            Container(blob, "Blob Storage", "Files & Logs", "Arquivos e logs de auditoria")
        }

        Deployment_Node(monitor, "Azure Monitor", "Observability") {
            Container(insights, "Application Insights", "APM & Logs", "Monitoramento e telemetria")
        }
    }

    Deployment_Node(external_mvp, "Sistemas Externos MVP", "Integrações Ativas") {
        Container(erp_ext, "ERP Senior", "Sistema Empresarial", "Dados de vendas e contratos")
        Container(support_ext, "Movidesk", "Sistema de Suporte", "Tickets e atendimentos")
        Container(powerbi_ext, "Power BI", "Business Intelligence", "Indicadores pré-processados")
    }

    Deployment_Node(external_v2, "Sistemas Externos V2", "Integrações Futuras") {
        Container(crm_ext, "CRM Eleve", "CRM", "Oportunidades e leads", $tags="future")
        Container(rd_ext, "RD Station", "Marketing", "Campanhas e automação", $tags="future")
    }

    Rel(browser, webapp, "Acessa sistema", "HTTPS")
    Rel(webapp, postgres, "Consultas SQL + KPIs", "TCP/TLS")
    Rel(webapp, blob, "Logs/Files", "HTTPS")
    Rel(webapp, powerbi_ext, "Consulta indicadores", "HTTPS")
    Rel(worker, postgres, "Processa dados + calcula KPIs", "TCP/TLS")
    Rel(worker, erp_ext, "Integração MVP", "HTTPS")
    Rel(worker, support_ext, "Integração MVP", "HTTPS")
    Rel(webapp, insights, "Telemetria", "HTTPS")

    Rel(worker, crm_ext, "Futuro: Integração CRM", "HTTPS", $tags="future")
    Rel(worker, rd_ext, "Futuro: Integração Marketing", "HTTPS", $tags="future")
```

### **4.3 Especificações de Infraestrutura (MVP)**

| Componente | Tipo | Especificação | Quantidade | Ambiente | Observações |
|------------|------|---------------|------------|----------|-------------|
| App Service | Azure Web App | Standard S1 (1 vCPU, 1.75GB) | 1 (auto-scaling) | Produção | Com KPI Engine integrado |
| WebJobs | Azure WebJobs | Integrado ao App Service | 2-5 workers | Produção | Para integrações MVP |
| PostgreSQL | Azure Database | Basic (2 vCores, 50GB) | 1 (backup geo) | Produção | Multi-tenant + KPIs |
| Blob Storage | Azure Storage | Standard LRS | 1 account | Produção | Logs e auditoria |
| Application Insights | Azure Monitor | Standard | 1 | Produção | Monitoramento completo |

**Recursos Removidos**: Conforme ADR-006, **Redis Cache não é utilizado** para simplificar a arquitetura.

### **4.4 Configurações de Rede**

| Recurso | Configuração | Descrição |
|---------|--------------|-----------|
| App Service | HTTPS Only | Força conexões seguras |
| Database | Private Endpoint | Acesso restrito via rede privada |
| Storage | Firewall Rules | Acesso apenas do App Service |
| Cache | TLS 1.2 | Conexões criptografadas |

### **4.5 Ambientes**

| Ambiente | Propósito | Infraestrutura | URL |
|----------|-----------|----------------|-----|
| Desenvolvimento | Desenvolvimento local | Docker Compose | http://localhost:3000 |
| Homologação | Testes integrados | Azure (reduzida) | https://csi-staging.azurewebsites.net |
| Produção | Ambiente final | Azure (completa) | https://csi.cliente.com.br |

---

## **5. Diagramas de Sequência**

### **5.1 Fluxo de Autenticação Multi-Tenant**

#### **5.1.1 Descrição**
Processo de login do usuário no sistema CSI com validação de tenant e perfil de acesso.

#### **5.1.2 Diagrama**

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend Vue
    participant API as Laravel API
    participant AUTH as AuthService
    participant DB as PostgreSQL
    participant CACHE as Redis

    U->>F: Acessa tela de login
    F->>U: Exibe formulário (email, senha, tenant)
    U->>F: Preenche credenciais
    F->>API: POST /api/auth/login
    
    API->>DB: Valida tenant ativo
    alt Tenant inativo
        DB-->>API: Tenant não encontrado/inativo
        API-->>F: Erro 401 - Tenant inválido
        F-->>U: Exibe mensagem de erro
    else Tenant válido
        API->>AUTH: Validar credenciais
        AUTH->>DB: SELECT user WHERE email = ?
        DB-->>AUTH: Dados do usuário
        
        alt Credenciais inválidas
            AUTH-->>API: Falha na autenticação
            API-->>F: Erro 401 - Credenciais inválidas
            F-->>U: Exibe mensagem de erro
        else Credenciais válidas
            AUTH->>DB: Verifica perfil e permissões
            DB-->>AUTH: Perfil do usuário
            AUTH->>API: Gera JWT token
            API->>CACHE: Armazena sessão
            API->>DB: Log de acesso
            API-->>F: Token JWT + dados do usuário
            F->>F: Armazena token no localStorage
            F-->>U: Redireciona para dashboard
        end
    end
```

### **5.2 Fluxo de Cálculo de KPI (MVP)**

#### **5.2.1 Descrição**
Processo de cálculo automático de KPI através de job assíncrono com integração de dados externos do MVP.

#### **5.2.2 Diagrama**

```mermaid
sequenceDiagram
    participant SCHED as Scheduler
    participant QUEUE as Queue Worker
    participant KPI as KPI Engine
    participant ERP as ERP Senior
    participant SUP as Movidesk
    participant PBI as Power BI
    participant DB as PostgreSQL
    participant LOG as AuditLog

    SCHED->>QUEUE: Dispara job de cálculo KPI
    QUEUE->>DB: Busca configuração do KPI
    DB-->>QUEUE: Configuração (fórmula, origem)
    
    alt Origem = ERP (MVP)
        QUEUE->>ERP: GET dados de vendas/contratos
        ERP-->>QUEUE: JSON/XML response
    else Origem = Movidesk (MVP)
        QUEUE->>SUP: GET tickets de suporte
        SUP-->>QUEUE: JSON response
    else Origem = Power BI (MVP)
        QUEUE->>PBI: GET indicadores pré-processados
        PBI-->>QUEUE: JSON response
    end
    
    QUEUE->>KPI: Processa dados conforme fórmula
    KPI->>KPI: Calcula valor do KPI
    
    rect rgb(255, 255, 0, 0.1)
        note right of KPI: Transação
        KPI->>DB: BEGIN TRANSACTION
        KPI->>DB: UPDATE kpi SET valor = ?
        KPI->>DB: INSERT INTO kpi_historico
        KPI->>LOG: Registra cálculo
        KPI->>DB: COMMIT
    end
    
    alt Cálculo com erro
        KPI->>DB: ROLLBACK
        KPI->>LOG: Registra erro
        QUEUE-->>SCHED: Job falhado (retry)
    else Cálculo bem-sucedido
        QUEUE-->>SCHED: Job concluído
        KPI->>QUEUE: Notifica atualização calculada
    end
```

### **5.3 Fluxo de Gestão de Tenants (HUB)**

#### **5.3.1 Descrição**
Processo de criação de novo tenant pelo usuário HUB com isolamento de dados.

#### **5.3.2 Diagrama**

```mermaid
sequenceDiagram
    participant HUB as Usuário HUB
    participant F as Frontend
    participant API as Laravel API
    participant DB as PostgreSQL
    participant QUEUE as Queue Worker
    participant LOG as AuditLog

    HUB->>F: Acessa gestão de tenants
    F->>API: GET /api/tenants
    API->>DB: SELECT tenants (apenas HUB)
    DB-->>API: Lista de tenants
    API-->>F: JSON response
    F-->>HUB: Exibe grid com filtros

    HUB->>F: Clica em "Novo Tenant"
    F-->>HUB: Abre modal
    HUB->>F: Preenche dados (nome, status)
    F->>API: POST /api/tenants

    API->>API: Valida permissão HUB
    API->>DB: Verifica nome único
    
    alt Nome já existe
        DB-->>API: Constraint violation
        API-->>F: Erro 422 - Nome já existe
        F-->>HUB: Exibe mensagem de erro
    else Nome disponível
        rect rgb(200, 255, 200, 0.1)
            note right of API: Criação do Tenant
            API->>DB: BEGIN TRANSACTION
            API->>DB: INSERT INTO tenants
            API->>DB: CREATE tenant schema/namespace
            API->>DB: INSERT admin user padrão
            API->>LOG: Registra criação
            API->>DB: COMMIT
        end
        
        API->>QUEUE: Dispara job de setup inicial
        API-->>F: Tenant criado com sucesso
        F->>F: Atualiza grid
        F-->>HUB: Exibe confirmação
        
        QUEUE->>DB: Cria estruturas básicas
        QUEUE->>LOG: Log de setup concluído
    end
```

### **5.4 Fluxo de Importação de Dados Externos (MVP)**

#### **5.4.1 Descrição**
Processo de importação de dados de sistemas externos MVP com tratamento de erro e log de auditoria (sem Redis conforme ADR-006).

#### **5.4.2 Diagrama**

```mermaid
sequenceDiagram
    participant CRON as Azure WebJob
    participant QUEUE as Queue Worker
    participant API as Laravel API
    participant ERP as ERP Senior
    participant SUP as Movidesk
    participant DB as PostgreSQL
    participant LOG as AuditLog

    CRON->>QUEUE: Agenda importação schedulada
    QUEUE->>API: Inicia processo de importação
    API->>DB: Busca configurações de integração MVP
    DB-->>API: Credenciais e endpoints (ERP + Movidesk)
    
    alt Sistema = ERP Senior
        API->>ERP: POST /auth authenticação
        ERP-->>API: Access token
        loop Para cada endpoint ERP
            API->>ERP: GET dados com paginação
            ERP-->>API: JSON/XML response
            
            alt Resposta com erro
                API->>LOG: Registra erro de integração ERP
                API->>QUEUE: Reagenda tentativa
            else Resposta válida
                API->>API: Transforma dados ERP
                API->>DB: Upsert registros (contratos, clientes)
            end
        end
    else Sistema = Movidesk
        API->>SUP: GET /tickets com token
        SUP-->>API: JSON response
        
        alt Resposta com erro
            API->>LOG: Registra erro de integração Movidesk
            API->>QUEUE: Reagenda tentativa
        else Resposta válida
            API->>API: Transforma dados tickets
            API->>DB: Upsert registros (tickets, interações)
            API->>LOG: Registra importação
        end
    end
    
    API->>DB: Atualiza timestamp da última sync
```
---

## **6. Diagrama de Classes (Principais Entidades)**

### **6.1 Descrição**
Representa as principais entidades do modelo de dados do sistema CSI e seus relacionamentos.

### **6.2 Diagrama**

```mermaid
classDiagram
    class Tenant {
        +int id
        +string nome
        +boolean ativo
        +timestamp created_at
        +timestamp updated_at
        +timestamp deleted_at
    }

    class User {
        +int id
        +int tenant_id
        +string name
        +string email
        +string password
        +enum perfil
        +boolean ativo
        +timestamp created_at
        +timestamp updated_at
    }

    class Cliente {
        +int id
        +int tenant_id
        +string nome
        +string cnpj_cpf
        +string email
        +enum tipo
        +enum status
        +json dados_adicionais
        +timestamp created_at
        +timestamp updated_at
        +timestamp deleted_at
    }

    class Kpi {
        +int id
        +int tenant_id
        +string nome
        +string descricao
        +enum tipo_calculo
        +enum origem_calculo
        +string formula
        +json configuracao
        +decimal valor_atual
        +timestamp ultima_atualizacao
        +boolean ativo
        +timestamp created_at
        +timestamp updated_at
        +timestamp deleted_at
    }

    class KpiHistorico {
        +int id
        +int kpi_id
        +decimal valor
        +json dados_calculo
        +timestamp periodo_inicio
        +timestamp periodo_fim
        +timestamp created_at
    }

    class Interacao {
        +int id
        +int tenant_id
        +int cliente_id
        +enum tipo
        +enum origem
        +string titulo
        +text descricao
        +json dados_origem
        +timestamp data_interacao
        +timestamp created_at
        +timestamp updated_at
    }

    class LogAuditoria {
        +int id
        +int tenant_id
        +int user_id
        +string acao
        +string entidade
        +int entidade_id
        +json dados_antes
        +json dados_depois
        +string ip_address
        +string user_agent
        +timestamp created_at
    }

    class IntegracaoConfig {
        +int id
        +int tenant_id
        +enum sistema
        +string endpoint
        +json credenciais
        +json configuracoes
        +boolean ativo
        +timestamp ultima_sync
        +timestamp created_at
        +timestamp updated_at
    }
```

### **6.3 Relacionamentos Principais**

| Relacionamento | Cardinalidade | Descrição |
|---------------|---------------|-----------|
| Tenant → User | 1:N | Um tenant possui muitos usuários |
| Tenant → Cliente | 1:N | Um tenant possui muitos clientes |
| Tenant → Kpi | 1:N | Um tenant possui muitos KPIs |
| Tenant → Interacao | 1:N | Um tenant possui muitas interações |
| Tenant → LogAuditoria | 1:N | Um tenant possui muitos logs |
| User → LogAuditoria | 1:N | Um usuário gera muitos logs |
| Cliente → Interacao | 1:N | Um cliente possui muitas interações |
| Kpi → KpiHistorico | 1:N | Um KPI possui muito histórico |

### **6.4 Enumerações Principais**

| Enum | Valores | Descrição |
|------|---------|-----------|
| user.perfil | HUB, ADMIN, USER | Perfis de acesso ao sistema |
| cliente.tipo | PF, PJ | Pessoa Física ou Jurídica |
| cliente.status | ATIVO, INATIVO, PROSPECT | Status do relacionamento |
| kpi.tipo_calculo | SOMA, MEDIA, CONTAGEM, PERCENTUAL | Tipo de cálculo |
| kpi.origem_calculo | ERP_SENIOR, MOVIDESK, POWER_BI, MANUAL | Origem dos dados (MVP) |
| interacao.tipo | VENDA, SUPORTE, MARKETING, OUVIDORIA | Tipo de interação |
| interacao.origem | ERP_SENIOR, MOVIDESK, MANUAL | Sistema de origem (MVP) |

---

## **7. Diagrama de Arquitetura Multi-Tenant**

### **7.1 Descrição**
Demonstra a estratégia de isolamento de dados multi-tenant utilizando shared database, shared schema.

### **7.2 Diagrama**

```mermaid
graph TB
    subgraph "Aplicação Laravel"
        TENANT_GUARD[Tenant Guard Middleware]
        TENANT_SCOPE[Global Tenant Scope]
    end
    
    subgraph "PostgreSQL Database"
        subgraph "Tabelas com tenant_id"
            T_USERS[users<br/>+tenant_id]
            T_CLIENTES[clientes<br/>+tenant_id] 
            T_KPIS[kpis<br/>+tenant_id]
            T_INTERACOES[interacoes<br/>+tenant_id]
            T_LOGS[logs_auditoria<br/>+tenant_id]
        end
        
        subgraph "Tabela Global"
            T_TENANTS[tenants<br/>sem tenant_id]
        end
    end
    
    subgraph "Requests de Usuários"
        REQ_A[Usuário Tenant A]
        REQ_B[Usuário Tenant B]
        REQ_C[Usuário Tenant C]
    end
    
    REQ_A --> TENANT_GUARD
    REQ_B --> TENANT_GUARD
    REQ_C --> TENANT_GUARD
    
    TENANT_GUARD --> TENANT_SCOPE
    TENANT_SCOPE --> T_USERS
    TENANT_SCOPE --> T_CLIENTES
    TENANT_SCOPE --> T_KPIS
    TENANT_SCOPE --> T_INTERACOES
    TENANT_SCOPE --> T_LOGS
    
    TENANT_GUARD -.-> T_TENANTS
    
    style T_TENANTS fill:#f9f,stroke:#333,stroke-width:2px
    style TENANT_GUARD fill:#bbf,stroke:#333,stroke-width:2px
    style TENANT_SCOPE fill:#bbf,stroke:#333,stroke-width:2px
```

### **7.3 Estratégia de Isolamento**

| Componente | Estratégia | Implementação |
|------------|------------|---------------|
| Database | Shared Database | Single PostgreSQL instance |
| Schema | Shared Schema | Todas as tabelas no mesmo schema |
| Data Isolation | Row Level Security | tenant_id em todas as tabelas |
| Application | Global Scope | Eloquent Global Scope automático |
| Authentication | Tenant Context | JWT token inclui tenant_id |
| Authorization | RBAC per Tenant | Permissões isoladas por tenant |

---

## **8. Diagrama de Fluxo de Monitoramento e Logs**

### **8.1 Descrição**
Fluxo de coleta, processamento e visualização de logs e métricas para usuários HUB.

### **8.2 Diagrama**

```mermaid
flowchart TD
    subgraph "Aplicação CSI"
        APP[Laravel Application]
        EVENTS[Application Events]
        LISTENERS[Event Listeners]
    end
    
    subgraph "Coleta de Dados"
        DB_LOG[Database Logs]
        APP_LOG[Application Logs]
        ACCESS_LOG[Access Logs]
        ERROR_LOG[Error Logs]
    end
    
    subgraph "Azure Monitor"
        INSIGHTS[Application Insights]
        LOG_ANALYTICS[Log Analytics]
        METRICS[Custom Metrics]
    end
    
    subgraph "Armazenamento"
        BLOB[Azure Blob Storage]
        DATABASE[(PostgreSQL)]
    end
    
    subgraph "Dashboard HUB"
        HUB_DASH[Dashboard de Logs]
        ALERTS[Alertas]
        REPORTS[Relatórios]
    end
    
    APP --> EVENTS
    EVENTS --> LISTENERS
    LISTENERS --> DB_LOG
    LISTENERS --> APP_LOG
    LISTENERS --> ACCESS_LOG
    
    APP --> ERROR_LOG
    
    DB_LOG --> DATABASE
    APP_LOG --> BLOB
    ACCESS_LOG --> INSIGHTS
    ERROR_LOG --> LOG_ANALYTICS
    
    INSIGHTS --> METRICS
    LOG_ANALYTICS --> METRICS
    
    DATABASE --> HUB_DASH
    BLOB --> HUB_DASH
    METRICS --> HUB_DASH
    
    METRICS --> ALERTS
    HUB_DASH --> REPORTS
    
    style APP fill:#e1f5fe
    style HUB_DASH fill:#f3e5f5
    style INSIGHTS fill:#fff3e0
    style DATABASE fill:#e8f5e8
```

### **8.3 Tipos de Logs Coletados**

| Tipo | Origem | Destino | Retenção |
|------|--------|---------|----------|
| Auditoria | User Actions | PostgreSQL | 2 anos |
| Aplicação | Laravel Logs | Blob Storage | 6 meses |
| Acesso | HTTP Requests | Application Insights | 3 meses |
| Performance | APM | Application Insights | 3 meses |
---

## **9. Diagrama de Integração com Sistemas Externos**

### **9.1 Descrição**
Fluxo de integração com os sistemas externos, mostrando as diferentes APIs e protocolos utilizados.

### **9.2 Diagrama**

```mermaid
graph TB
    subgraph "CSI System"
        SCHEDULER[Azure WebJob Scheduler]
        QUEUE[Queue Workers]
        CONFIG[Integração Config]
        TRANSFORMER[Data Transformer]
        VALIDATOR[Data Validator]
        KPI_ENGINE[KPI Engine]
    end
    
    subgraph "Sistemas MVP"
        ERP_SENIOR[ERP Senior<br/>REST/SOAP API<br/>]
        MOVIDESK[Movidesk<br/>REST API<br/>]
        POWER_BI[Power BI<br/>REST API<br/>]
    end
    
    subgraph "Sistemas V2 (Futuro)"
        CRM_ELEVE[CRM Eleve<br/>REST API<br/>]
        RD_STATION[RD Station<br/>REST API<br/>]
    end
    
    subgraph "Processamento (Sem Redis)"
        RATE_LIMIT[Rate Limiting]
        RETRY_LOGIC[Retry Logic]
        ERROR_HANDLER[Error Handler]
        SESSION_DB[Database Sessions]
    end
    
    SCHEDULER --> QUEUE
    QUEUE --> CONFIG
    CONFIG --> SESSION_DB
    
    SESSION_DB --> RATE_LIMIT
    RATE_LIMIT --> ERP_SENIOR
    RATE_LIMIT --> MOVIDESK
    RATE_LIMIT --> POWER_BI
    
    ERP_SENIOR --> VALIDATOR
    MOVIDESK --> VALIDATOR
    POWER_BI --> VALIDATOR
    
    VALIDATOR --> TRANSFORMER
    TRANSFORMER --> KPI_ENGINE
    KPI_ENGINE --> DATABASE[(PostgreSQL)]
    
    CRM_ELEVE -.-> VALIDATOR
    RD_STATION -.-> VALIDATOR
    
    VALIDATOR -.-> ERROR_HANDLER
    RATE_LIMIT -.-> RETRY_LOGIC
    ERROR_HANDLER -.-> RETRY_LOGIC
    
    style SCHEDULER fill:#e3f2fd
    style QUEUE fill:#e8f5e8
    style ERROR_HANDLER fill:#ffebee
    style SESSION_DB fill:#f3e5f5
    style ERP_SENIOR fill:#90EE90
    style MOVIDESK fill:#FFB6C1
    style POWER_BI fill:#87CEEB
    style CRM_ELEVE fill:#D3D3D3,stroke-dasharray: 5 5
    style RD_STATION fill:#D3D3D3,stroke-dasharray: 5 5
```

### **9.3 Especificações de Integração (MVP)**

**Integrações Ativas (MVP):**
| Sistema | Protocolo | Autenticação | Frequência | Rate Limit | Funcionalidade |
|---------|-----------|--------------|------------|------------|----------------|
| ERP Senior | REST/SOAP | API Key + OAuth | 4x/dia | 100 req/min | Contratos, clientes, vendas |
| Movidesk | REST | Token | 6x/dia | 120 req/min | Tickets, interações |
| Power BI | REST | Bearer Token | Sob demanda | 60 req/min | Indicadores pré-processados |

**Integrações Futuras (V2):**
| Sistema | Protocolo | Autenticação | Frequência | Rate Limit | Funcionalidade |
|---------|-----------|--------------|------------|------------|----------------|
| CRM Eleve | REST | Bearer Token | 2x/dia | 60 req/min | Oportunidades, leads |
| RD Station | REST | OAuth 2.0 | 1x/dia | 300 req/hora | Campanhas, automação |

**Nota**: Conforme ADR-006, não há cache Redis - todas as sessões e dados temporários são gerenciados via PostgreSQL.

---

## **10. Considerações de Implementação**

### **10.1 Validação dos Diagramas**
Todos os diagramas apresentados neste documento foram validados contra:
- ✅ Documento de Arquitetura de Software (v2.0)
- ✅ Documentação de Requisitos do CSI (v1.0)
- ✅ ADR-006 (Decisão de não usar Redis)
- ✅ Configurações de infraestrutura Azure
- ✅ Escopo MVP vs V2 definido

### **10.2 Ferramentas Utilizadas**
- **Mermaid**: Para criação de todos os diagramas
- **C4 Model**: Para diagramas de contexto e componentes
- **UML**: Para diagramas de sequência e classes
- **Markdown**: Para documentação estruturada

### **10.3 Manutenção dos Diagramas**
Os diagramas devem ser atualizados sempre que houver:
- Mudanças na arquitetura do sistema
- Novas integrações com sistemas externos
- Alterações nos fluxos de negócio
- Modificações na infraestrutura Azure
- Atualizações de tecnologias (Laravel, Vue.js, etc.)

### **10.4 Próximos Passos**
1. **Validação com a Equipe**: Revisar diagramas com desenvolvedores
2. **Documentação de APIs**: Detalhar especificações das APIs REST
3. **Diagramas de Segurança**: Adicionar fluxos específicos de segurança
4. **Performance**: Incluir diagramas de otimização e cache
5. **Disaster Recovery**: Documentar procedimentos de backup/restore

### **10.5 Referências**
- [Documento de Arquitetura de Software v2.0](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Documentação de Requisitos do CSI v1.0](./1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
- [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)
- [Sistema de Permissões](./6%20Sistema%20de%20Permissões.md)
- [C4 Model Documentation](https://c4model.com/)
- [Mermaid Documentation](https://mermaid.js.org/)
- [Laravel 12 Architecture](https://laravel.com/docs/12.x/architecture)
- [Vue.js 3 Guide](https://vuejs.org/guide/)
- [Azure Architecture Center](https://docs.microsoft.com/en-us/azure/architecture/)
- [Power BI REST API](https://docs.microsoft.com/en-us/rest/api/power-bi/)
