## Documento de Requisitos de Software (DRS) - CSI - Customer Success Intelligence

### **Informações do Documento**

*   **Projeto:** CSI - Customer Success Intelligence
*   **Versão:** 1.0 
*   **Data:** 29/09/2025
*   **Autor(es):** Luan Felipe Tavares
*   **Revisor(es):** 
*   **Aprovador(es):** 

### **Documentos Relacionados**
- [Documento de Arquitetura de Software](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Diagramas Arquiteturais](./3%20Diagramas%20Arquiteturais.md)
- [Especificações Técnicas](./4%20Especificações%20Técnicas.md)
- [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)
- [Sistema de Permissões](./6%20Sistema%20de%20Permissões.md)
- [Guia de Comandos Docker](./7%20Guia%20de%20Comandos%20Docker.md)

### **Histórico de Revisões**

| Versão | Data | Autor | Descrição das Alterações |
| :--- | :--- | :--- | :--- |
| 0.1 | 27/06/2025 | Luan Felipe Tavares | Versão inicial para coleta de requisitos |
| 0.2 | 20/08/2025 | Luan Felipe Tavares | Adicionado novas Histórias na gestão de clientes |
| 0.3 | 15/09/2025 | Luan Felipe Tavares | Alterado uso de modal para tela específica |
| 0.4 | 15/09/2025 | Luan Felipe Tavares | Adicionado novas Interações e requisitos após alinhamento com a Direção |
| 0.5 | 19/09/2025 | Luan Felipe Tavares | Adicionado novos Requisitos Funcionais e Removido os que precisam ser reescritos |
| 0.6 | 29/09/2025 | Luan Felipe Tavares | Adicionado Finalizado requisitos da gestão de KPI |

---

### **1. Introdução**

#### **1.1 Finalidade**
O objetivo deste documento é detalhar os requisitos funcionais e não-funcionais para o sistema **Customer Success Intelligence (CSI)**. Ele servirá como guia para as equipes de desenvolvimento, testes e produto durante todo o ciclo de vida do projeto.

#### **1.2 Escopo do Produto**
* **O que está DENTRO do escopo [MVP]:**
    - Gestão de Segurança (Tenants, Perfis, Usuários, Permissões)
    - Gestão de Clientes (Perfil do cliente, Gestão de Contatos)
    - Gestão de Interações (Importação de interações dos sistemas legados, Cadastro manual)
    - Monitoramento (Gestão de LOGs)
    - Indicadores do BI (Módulos, Mensalidades, Custo de Servir, etc.)

* **O que está FORA do escopo:**
    - Aplicativo Mobile
    - Integração com WhatsApp
    - Gerenciamento financeiro do cliente
    - Integração com outras origens não listadas

* **O que está em validação:**
    - RF-015: Espelhamento de contatos para sistemas legados [EM VALIDAÇÃO]

* **O que pode ser implementado no futuro:**
    - Gestão de KPIs (criação, cálculo e visualização)
    - Agentes de IA para extração de informações
    - Planos de Ação
    - Novos conectores e integrações

#### **1.3 Definições, Acrônimos e Abreviações**
| Termo | Definição |
| :--- | :--- |
| **CSI** | Customer Success Intelligence |
| **KPI** | Key Performance Indicator (Indicador-Chave de Desempenho) |
| **SLA** | Service Level Agreement (Acordo de Nível de Serviço) |
| **MRR** | Monthly Recurring Revenue (Receita Mensal Recorrente) |
| **RBAC**| Role-Based Access Control (Controle de Acesso Baseado em Papéis) |
| **MVP** | Minimum Viable Product (Produto Mínimo Viável)|

---

### **2. Descrição Geral**

#### **2.1 Perfis de Usuário (Atores)**
A seguir, estão os perfis que interagirão com o sistema.

| Ator | Descrição e Objetivos Principais | Nível de Conhecimento Técnico |
| :--- | :--- | :--- |
| **HUB** | Gerencia os tenants, monitora Logs, acessa qualquer cliente. | Alto |
| **Administrador** | Gerencia as permissões de acesso do tenant. | Médio |
| **Usuario** | Acessa e utiliza as rotinas liberadas. | Baixo |
| **CSI** | Ator que representa o sistema e suas tarefas agendadas. | N/A (Sistema)
| **Sistema Legado** | Fonte externa de dados que envia informações de interações para o CSI. | N/A (Sistema) |

---

### **3. Requisitos Funcionais (Histórias de Usuário)**

#### **Módulo: Gestão de Acesso e Permissões**

**RF-001: Gerenciar Tenants**
*   **História:** Como um usuário com o perfil **HUB**, eu quero **criar ou editar um tenant** (ex: "sancon"), para que **eu possa alterar as configurações do tenant**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve haver um formulário mostrando os tenants já cadastrados, podendo pesquisa pelo nome e status.
        2.  Deve haver um botão para adicionar um novo tenant.
        3.  Ao clicar em novo tenant ou em um tenant já existente, deve navegar para uma página de edição dos dados.
        4.  Ao inserir um tenant o campo status fica oculto
    2.  Campos
        1.  Código, Nome, Domínio, Status
        2.  O nome do tenant deve ser único no sistema.
    3.  Comportamento
        1.  O sistema deve registrar por quem e quando o tenant foi alterado.
        2.  Ao inserir um novo tenant
            1.  Deve ser criado já os perfis HUB, Administrador e Usuário
            2.  Deve ser criado um usuário administrador para o tenant e vincular ao perfil Administrador
            3.  O campo status deve ser inserido como ativo
            4.  Deve criar os tipos de interação padrão
                1.  Faturamento MRR
                2.  Faturamento Serviço
                3.  Orçamentos Serviços
                4.  Propostas Comerciais
                5.  Inadimplencia
                6.  Tempo de contrato
                7.  Projetos em aberto
                8.  Visita 
                9.  Oportunidades
                10. Ticket
                11. Ouvidoria Senior
                12. Pesquisa de Satisfação Sancon NPS
                13. CSAT
                14. Status Report
                15. Passagem Suporte
                16. NPS Senior
                17. Registro de Atuação Pirata
                18. Acionamento Gestão
                19. Acionamento Direção
                20. Kickoff interno
            5.  Criar os conectores 1-ERP, 2-Movidesk e 3-CRM Eleve
        3.  Ao alterar o status de um tenant para inativo
            1.  Deve inativar todos os usuários do tenant
        4.  Ao finalizar a edição ou criação deve retornar para a lista de tenants com os dados atualizados

**RF-002: Gerenciar Usuários**
*   **História:** Como um usuário com o perfil **Administrador**, eu quero **criar ou editar um novo usuário** (ex: "luan.tavares"), para que **eu possa disponibilizar o acesso ao sistema**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve haver um formulário mostrando os usuários já cadastrados, podendo pesquisa pelo nome, login e status
        2.  Deve haver um botão para adicionar um novo usuário.
        3.  Ao clicar em novo usuário ou em um usuário já existente, deve navegar para uma página de edição dos dados.
        4.  Na página de edição deve existir um botão para abrir a gestão de perfis.
        5.  Se o usuário logado for do perfil HUB, mostra o campo tenant e o deixa como obrigatório
        6.  Se o usuário logado não for do tenant, oculta o tenant
    2.  Campos
        1.  Nome, Usuário, Dominio, eMail, Senha, Status.
    3.  Comportamento
        1.  O sistema deve registrar quem e quando o usuário foi alterado.
        2.  O login do usuário deve ser único no sistema.
        3.  Ao finalizar a edição ou criação deve retornar para a lista de usuários com os dados atualizados
        4.  Se o usuário logado não for do perfil HUB insere no tenant do usuário novo, o tenant do usuário logado
    

**RF-003: Gerenciar Perfis**
*   **História:** Como um usuário com perfil **Administrador ou HUB**, eu quero **criar ou editar um novo perfil de usuário** (ex: "Analista de Sucesso Jr."), para que **eu possa parametrizar as permissões desse perfil**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve haver um formulário mostrando os perfis já cadastrados, podendo pesquisa pelo nome e status
        2.  Deve haver um botão para adicionar um novo perfil.
        3.  Ao clicar em novo perfil ou em um perfil já existente, deve navegar para uma página de edição dos dados.
        4.  Na página de edição deve existir 
            1.  Lista com os usuários associados ao perfil, bem como os disponíveis, podendo adicionar ou remover os usuários do perfil.
            2.  Lista com os KPIs associados ao perfil, bem como os disponíveis, podendo adicionar ou remover os KPIs do perfil.
            3.  Lista com as permissões das telas que o perfil tem acesso
            4.  O campo de tenant só deve ser visível e obrigatório para o usuário que pertencer ao perfil HUB
    2.  Campos
        1.  Codigo, Nome, Descrição, Tenant
        2.  O nome do perfil deve ser único no sistema.
    3.  Comportamento
        1.  Ao finalizar a edição ou criação deve retornar para a lista de perfis com os dados atualizados
        2.  O sistema deve registrar quem e quando o perfil foi criado.
        3.  Os perfis **HUB** e **Administrador** não devem mostrar a lista dos KPIs e nem permitir alterações no que tange a nome e descrição, apenas vincular a usuário.
            1.  O perfil HUB só pode ser vinculado a um usuário que pertence ao perfil HUB
        4.  Se o usuário logado não for do perfil HUB insere no tenant do perfil novo, o tenant do usuário logado
        5.  Ao listar os usuário só deve aparecer os usuários do tenant
        6.  O tenant do perfil nunca pode ser alterado, o mesmo é definido na criação e nunca mais se altera

#### **Módulo: Gestão de KPIs**

**RF-004: Gerir KPIs**
*   **História:** Como um **Usuário** com as devidas permissões, eu quero **criar ou editar um KPI** (ex: "MRR"), para que **eu possa disponibilizar este KPI para ser vinculado aos Perfis**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve existir um formulário com os KPIs já cadastrados, podendo pesquisar pela descrição
        2.  Deve existir um botão para poder inserir um novo KPI
        3.  Deve existir um formulário para edição/criação de KPIs
            1.  Formulário deve ter uma aba para os dados gerais do KPI
            2.  Formulário deve ter uma aba para cadastrar as configurações
                1.  Dentro desse formulário deve ter os dados da configuração e uma GRID com a base
                2.  O id Tipo Interação só deve aparecer se o Tipo da base for T - Tipo Interação
                3.  O id KPI só deve aparecer se o tipo da base for K - KPI
                4.  O campo fórmula, só deve ser apresentado quando o campo opração for Fórmula
    2.  Campos
        1.  Serão necessárias três tabelas para o processo
        2.  KPI
            1.  Tennant, ID, Descrição
        3.  Configuração
            1.  Tennant, ID, Data (Histórico), CRON (Calculo), Operação (Enumerador Fixo), Fórmula
                1.  Operação deve ter as seguintes opções (Soma, Média, Mínimo, Máximo, Contagem, Fórmula)
            2.  Onde o tenant, id do kpi e data são campos chave
        4.  Base
            1.  Tennant, ID, Data (Histórico), Sequência, Tipo (T - Tipo Interação, K - KPI), id Tipo Interação, id KPI
            2.  Onde o tenant, id do kpi, data e sequência são campos chave
    3.  Comportamento
        1.  Na edição do campo fórmula, devem aparecer apenas as opções:
            1.  Operações matemáticas (=,-,*,/)
            2.  Agrupadores ()
            3.  Variáveis, que serão as opções cadastradas na base 
            4.  Deve permitir valores fixos, por exemplo 100
                1.  ((KPI1 * KPI2) / 100)
        2.  Ao salvar uma alteração na configuração, deve validar: 
            1.  Referência circular (um item da base, depender do próprio KPI)
            2.  Sintaxe Matemática


**RF-005: Calcular KPIs**
*   **História:** Como **CSI**, eu quero **calcular os KPIs** (ex: "MRR"), para que **os usuários possam ver o valor do KPI**.
*   **Critérios de Aceite:**
    1.  Campos
        1.  Tennant, ID, ID Cliente, data calculo, valor calculado, status ('CALCULANDO', 'SUCESSO', 'ERRO', 'PENDENTE_DEPENDENCIA'), tempo de execução
        2.  Onde Tennant, ID, ID Cliente e data calculo são chave
    2.  Comportamento
        1.  Serão dois jobs
            1.  Job Orquestrador
                1.  Fixado em código o seu CRON, valor padrão deve ser a cada 1 hora
                2.  Deve percorrer TODOS os tenants ativos do sistema
                3.  Para cada tenant, deve verificar se existem KPIs que precisam ser calculados
                4.  Deve usar a expressão CRON de cada configuração para determinar se é hora de calcular
                5.  Deve analisar a dependencia de cada KPI e definir a ordem de calulo
                6.  Deve buscar todos os clientes ativos
                7.  Deve registrar na fila de calculo (Tennants {KPIs{} (odenados), Clientes{}})
                8.  Deve registrar log de execução (início, fim, tenants processados, erros)
                9.  Deve tratar erros sem interromper o processamento de outros tenants
            2.  Job Calculo
                1.  Deve monitorar a fila de calculo e para cada pendência processar o calculo
                2.  Para cada KPI deve:
                    1.  Percorrer a lista de cliente, e para cada um, deve:
                        1.  Inserir na tabela KPI_Calculado o registro do calculo
                        2.  Marcar status como 'CALCULANDO'
                        3.  Executar o cálculo conforme a operação configurada
                        4.  Salvar o resultado ou erro
                        5.  Atualizar status final
                        6.  Registrar tempo de execução
                        7.  Alterar status de acordo com a situação erro, sucesso, pendência de dependência
                    2.  Fazer o calculo do mesmo sem cliente
                3.  Remover a pendência da fila.
        2.  O calculo do KPI propriamente dito, deve ser um service, que recebe o tenant, id do KPI e o cliente.

**RF-006: Visualizar Dashboard**
*   **História:** Como **usuario**, eu quero **consultar todos os KPIs aos quais eu tenho acesso**, para que **eu possa analisar os KPI**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve existir um formulário mostrando no formato de cards cada KPI que o usuário tem acesso.
        2.  Ao acessar, mostra os KPIs de todos os clientes com base na data atual
        3.  Deve ser possível selecionar um cliente específico ou todos.
        4.  Deve ser possível selecionar uma data de referência.
        5.  Deve tratar KPIs sem valor

#### **Módulo: Importação**

**RF-007: Gerenciar Conectores**
*   **História:** Como um usuário com o perfil **Administrador**, eu quero **configurar um adaptador** (ex: "ERP", "Movidesk"), para que **as interações possam ser importadas**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve existir um formulário mostrando os conectores disponíveis para configuração
        2.  Não deve ser possível caadstrar um novo conector
        3.  Ao clicar em um conector deve navegar para uma página de edição dos dados.
    2.  Campos
        1.  Tenant, Código, URL, usuário e senha (1-ERP), token (2-Movidesk)

**RF-009: Importar Interações do ERP**
*   **História:** Como **CSI**, eu quero **processar a importação das interações oriundas do ERP** (ex: "Faturamento"), para que **as interações estejam disponíveis para consulta centralizada**.
*   **Critérios de Aceite:**
    1.  Deve existir um serviço que percorra todos os tipos de interação (RF-018) que tenham o conector 1-ERP
    2.  O serviço deve ser capaz de executar um webservice soap na base do ERP
        1.  Utilizando o campo URL do conector (ex: http://example.com/g5-senior-services/)
        2.  Utilizando a porta do tipo de interação (ex: sapiens_Synccom_senior_g5_co_ger_relatorio)
        3.  Utilizando o usuário e senha do conector
    3.  O serviço deve usar o WSDLHelper, para já ter os dados retornados no formato jSon.
    4.  Todos os WebServices no Senior devem já retornar os dados na seguinte estrutura.
        1.  SaiTabResCon
            1.  SaiIntCodCli
            2.  SaiDatDatItr
            3.  SaiStrTitItr
            4.  SaiStrDesItr
            5.  SaiDecVlrItr
    5.  Para cada registro retornado pelo ERP, deve gravar a interação do respectivo tipo
    6.  Registrar LOG 
        1.  No inicio do processamento
        2.  Dos registros que tiverem erro
        3.  Do fim do processamento x Registros com sucesso e Y com erro

**RF-010: Importar Interações do Movidesk**
*   **História:** Como **CSI**, eu quero **processar a importação das interações oriundas do Movidesk** (ex: "Tickets"), para que **as interações estejam disponíveis para consulta centralizada**.
*   **Critérios de Aceite:**
    1.  Deve existir um serviço que percorra todos os tipos de interação (RF-018) que tenham o conector 2-Movidesk
    2.  O serviço deve executar a query de consultas de tickets do movidesk
        1.  Utilizando o campo URL do conector (ex:https://api.movidesk.com)
        2.  Utilizando a rota do tipo de interação (ex:/public/v1/tickets)
        3.  Utilizando o token do conector
        4.  Passando de forma fixa o parâmetro $select com o valor
            1.  id,subject,createdDate,ownerTeam,status,urgency
        5.  Passando de forma fixa o parâmetro $filter 
            1.  createdDate ge 2025-07-16T00:00:00.00Z and createdDate le 2025-07-16T12:00:00.00Z
            2.  onde a data deve ser o dia anterior completo
    3.  Para cada registro retornado pelo movidesk, deve gravar a interação do respectivo tipo
    4.  Registrar LOG 
        1.  No inicio do processamento
        2.  Dos registros que tiverem erro
        3.  Do fim do processamento x Registros com sucesso e Y com erro

**RF-011: Importar Interações do CRM Eleve**
*   **História:** Como **CSI**, eu quero **processar a importação das interações oriundas do CRM Eleve** (ex: "Oportunidades"), para que **as interações estejam disponíveis para consulta centralizada**.
*   **Critérios de Aceite:**
    1.  Deve existir um serviço que percorra todos os tipos de interação (RF-018) que tenham o conector 3-CRM Eleve
    2.  
    5.  Para cada registro retornado pelo senior, deve gravar a interação do respectivo tipo
    6.  Registrar LOG 
        1.  No inicio do processamento
        2.  Dos registros que tiverem erro
        3.  Do fim do processamento x Registros com sucesso e Y com erro

#### **Módulo: Gestão de Interações**

**RF-013: Gerenciar Interação**
*   **História:** Como um **Usuário** com a devida permissão, eu quero **cadastrar/alterar uma interação de forma manual** (ex: "Acionamento Gestão", "Ouvidoria"), para que **a informação da interação fique registrada**.
*   **Critérios de Aceite:**
    1.  Interface
        2.  Deve haver um formulário mostrando as interações já cadastradas, podendo pesquisar por Tipo, Data, Descrição, Cliente.
        2.  Deve haver um botão para adicionar uma nova interação.
        3.  Ao clicar em nova interação ou em uma interação já existente, deve navegar para uma página de edição dos dados.
    2.  Campos
        1.  Tenant, Cliente, chave (forma de identificar o registro na origem, ex: Código do ticket, numero da nota e etc), Tipo, Data, Titulo, Descrição (Texto Longo), Valor
    3.  Comportamento
        1.  Ao salvar, deve retornar para a página de listagem.
        2.  Ao finalizar a edição ou criação deve retornar para a lista de interações com os dados atualizados

**RF-018: Gerenciar Tipos de Interação**
*   **História:** Como um usuário com o perfil **Administrador**, eu quero **cadastrar/alterar um tipo de interação** (ex: "Acionamento Gestão", "Ouvidoria"), para que **eu possa registrar interações deste tipo**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve haver um formulário mostrando os tipos já cadastrados, podendo pesquisar pelo nome
        2.  Deve haver um botão para adicionar um novo tipo.
        3.  Ao clicar em novo tipo ou em um tipo já existente, deve navegar para uma página de edição dos dados.
        4.  Deve existir um botão para disparar a importação manualmente. Botão só pode estar ativo se algum conector estiver selecionado
        5.  As informações de configuração, porta, rota e etc, só devem ser apresentadas se for selecionado um conector
    2.  Campos
        1.  Tenant, código, nome, conector, porta (para o conector 1-ERP), rota (para o conector 2-Movidesk)
    3.  Comportamento
        1.  O sistema deve registrar quem e quando o tipo criado/editado.
        2.  Ao finalizar a edição ou criação deve retornar para a lista de tipos com os dados atualizados
  

#### **Módulo: Gestão de Clientes**

**RF-014: Gerenciar Clientes**
*   **História:** Como um **Usuário** com a devida permissão, eu quero **consultar ou alterar alguma informação do cliente** (ex: "Nome"), para que **o cadastro seja atualizado**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve haver um formulário mostrando os clientes já cadastrados, podendo filtrar por nome fantasia, código, cnpj/cpf, código cliente senior, status.
        2.  Ao clicar em um cliente, deve navegar para uma página de edição com todos os dados do mesmo.
        3.  Deve ter um botão para acessar a RF-013 filtrando já o cliente.
        4.  Deve ter um botão para acessar a RF-015 com o tenant e o cliente já selecionado.
        5.  O campo Tenant só deve aparacer para usuários vinculados ao perfil HUB.
    2.  Campos
        1.  Tenant, Razão Social, Nome Fantasia, código, cpf/cnpj, código cliente senior, status, Cliente Referencia (S/N), Tipo do Perfil (Relacional/Transacional), Promotor (S/N)
        2.  O cpf/cnpj deve ser único no sistema
    3.  Comportamento
        1.  O sistema deve registrar quem e quando ao cliente foi editado.
        2.  Ao finalizar a edição ou criação deve retornar para a lista de clientes com os dados atualizados
        3.  Ao inserir um novo cliente se o usuário não for do perfil HUB, insere como tenant, o tenant do usuário logado

**RF-015: Gerenciar Contatos**
*   **História:** Como um **Usuário** com a devida permissão, eu quero **registrar/alterar um contato do cliente** (ex: "Adicionar o contato de um novo gerente"), para que **o cadastro seja atualizado**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Todos os campos devem estar habilitados posterior seleção do tenant e cliente
            1.  Se o usuário logado não pertence ao perfil HUB, mostra o campo tenant, caso contrário, o campo deve ficar oculto
            2.  Ao selecionar o cliente, carrega os contatos de acordo com os filtros
        2.  Deve haver um formulário mostrando os contatos já cadastrados, podendo filtrar por nome, email ou código.
        3.  Ao clicar em um contato, deve navegar para uma página de edição com todos os dados do mesmo.
        4.  Deve existir também um botão para adicionar um novo contato, que navega para uma página de criação.
    2.  Campos
        1.  Tenant, Código, Nome, eMail, Cargo, Telefone, Tipo do Perfil (Relacional/Transacional), Promotor (S/N)
    3.  Comportamento
        1.  Ao finalizar a edição ou criação deve retornar para a lista de contatos com os dados atualizados
        2.  O sistema deve registrar quem e quando ao contato foi criado/editado.
        3.  Ao salvar um contato, faz-se necessário atualizar os demais sistemas com essa informação (ERP, CRM Eleve, CRM Senior, Movidesk, RD Station).

#### **Módulo: Monitoramento**

**RF-016: Gerenciar LOGs**
*   **História:** Como um usuário com o perfil **HUB**, eu quero **analisar os logs**, para que **eu obtenha informações sobre a saúde do sistema**.
*   **Critérios de Aceite:**
    1.  Interface
        1.  Deve haver um formulário mostrando os LOGs registrados em formato de dashboard, mostrando quantas interações foram cadastradas nas ultimas 24 horas, quantos logs foram registrados por ação, quantos logs de erro.
        2.  Deve ser possível pesquisar os logs por ação, tipo (informativo ou erro), período, conteúdo. 
    2.  Campos
        1.  Tenant, usuário, ação, texto do log, data e hora, tipo ou status (diferenciar log informativo de erro)
    3.  Comportamento
        1.  Ao filtrar deve mostrar os logs encontrados em formato de lista, clicando no log deve navegar para uma página de detalhes com todas as informações do LOG.
 

#### **Módulo: Indicadores**
**RF-017: Gestão de Indicadores (Power BI)**
*   **Histótia:** Como um **Usuário** com a devida permissão, quero **consultar os indicadores do BI** (exemplo: Módulos, Mensalidades), para que eu possa **acompanhar os indicadores**
*   **Critérios de Aceite:**
    1. Interface
       1. Deve haver um formulário para selecionar o cliente 
       2. Deve apresentar os indicadores do BI
    2. Indicadores
       1. Módulos
       2. Mensalidade
       3. Custo de Servir
       4. Módulos Cancelados
       5. Inadimplência 
    3. Comportamento
       1. Ao selecionar o cliente deve buscar no BI os indicadores filtrando o mesmo


---

### **4. Requisitos Não-Funcionais**

Estes requisitos descrevem *como* o sistema deve operar, definindo seus atributos de qualidade.

| Categoria | Requisito | Métrica de Sucesso (Como saberemos que foi atendido?) |
| :--- | :--- | :--- |
| **Desempenho** | O dashboard principal deve carregar rapidamente. | O tempo de carregamento completo do dashboard deve ser **inferior a 5 segundos**. |
| **Desempenho** | A importação de dados não deve travar o sistema. | O sistema deve ser capaz de processar **120 interações por minuto** de forma assíncrona. |
| **Segurança** | O acesso aos dados deve ser restrito ao perfil do usuário. | Um usuário com perfil de "Suporte" **não pode** acessar as funcionalidades de "Administrador". |
| **Disponibilidade** | O sistema deve estar disponível para os usuários durante o horário comercial. | O uptime do sistema deve ser de **95%** medido mensalmente. |
| **Usabilidade** | A interface para criar um novo KPI deve ser intuitiva. | Um novo usuário **Administrador**, sem treinamento, deve ser capaz de criar um KPI simples em **menos de 5 minutos**. |

