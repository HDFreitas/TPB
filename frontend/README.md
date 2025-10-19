# Documentação do Template Vue.js

Este template foi criado para ser um ponto de partida robusto e organizado para novas aplicações Vue.js 3, utilizando Vite, TypeScript, Vuetify 3 e Pinia.

## Tecnologias Principais:

*   **Vue.js 3:** Framework JavaScript progressivo.
*   **Vite:** Build tool rápida para desenvolvimento web moderno.
*   **TypeScript:** Superset do JavaScript que adiciona tipagem estática.
*   **Vuetify 3:** Biblioteca de componentes UI Material Design para Vue.
*   **Pinia:** Gerenciador de estado intuitivo para Vue.
*   **Vue Router:** Roteamento oficial para Vue.js.
*   **SCSS:** Pré-processador CSS para estilos mais organizados.

## Requisitos:
 - Node.js 20 ou superior
 - Npm 10.8.2 ou superior

## Como Começar:

1.  **Instalar Dependências:**
    ```bash
    npm install
    # ou
    yarn install
    ```
2.  **Rodar Servidor de Desenvolvimento:**
    ```bash
    npm run dev
    # ou
    yarn dev
    ```
3.  **Compilar para Produção:**
    ```bash
    npm run build
    # ou
    yarn build
    ```

## Estrutura de Pastas e Arquivos:

Aqui está uma visão geral do que cada parte do projeto faz:

```
├── README.md               # Este arquivo de documentação inicial
├── env.d.ts                # Declarações de tipos para variáveis de ambiente Vite
├── index.html              # Ponto de entrada HTML principal
├── package-lock.json       # Lockfile do npm
├── package.json            # Dependências e scripts do projeto
├── public/                 # Arquivos estáticos (não processados pelo Vite)
│   └── vite.svg            # Exemplo de asset público
├── src/                    # Código fonte da aplicação
│   ├── App.vue             # Componente Vue raiz da aplicação
│   ├── assets/             # Assets processados pelo build (imagens, fontes, etc)
│   │   └── images/         # Imagens organizadas por tipo
│   ├── components/         # Componentes Vue reutilizáveis
│   │   ├── HelloWorld.vue  # Exemplo de componente genérico
│   │   └── auth/           # Componentes específicos da funcionalidade de autenticação
│   │       └── LoginForm.vue # Formulário de login
│   ├── layouts/            # Estruturas de página (templates)
│   │   ├── blank/          # Layouts sem elementos comuns (ex: login, erro)
│   │   │   └── BlankLayout.vue
│   │   └── full/           # Layout principal da aplicação (com header, sidebar, etc)
│   │       ├── FullLayout.vue # Componente principal do layout completo
│   │       ├── Main.vue      # Container principal do conteúdo
│   │       ├── logo/         # Componentes de logo (claro/escuro)
│   │       ├── vertical-header/ # Componentes do cabeçalho (notificações, perfil)
│   │       └── vertical-sidebar/ # Componentes da barra lateral (itens de menu, grupos)
│   ├── main.ts             # Ponto de entrada da aplicação (inicializa Vue, plugins)
│   ├── plugins/            # Configuração de plugins Vue (ex: Vuetify)
│   │   └── vuetify.ts      # Configuração do Vuetify
│   ├── router/             # Configuração do Vue Router
│   │   ├── guard.ts        # Lógica de proteção de rotas (autenticação)
│   │   ├── index.ts        # Arquivo principal do roteador
│   │   └── modules/        # Rotas modularizadas por funcionalidade (auth, main)
│   ├── services/           # Lógica de comunicação com APIs
│   │   └── api.ts          # Configuração do cliente HTTP (ex: Axios)
│   ├── shims-vue.d.ts      # Shims para reconhecimento de arquivos .vue pelo TypeScript
│   ├── stores/             # Módulos de estado do Pinia
│   │   ├── auth.ts         # Store para estado de autenticação
│   │   └── counter.ts      # Exemplo de store
│   ├── style.css           # Estilos CSS globais básicos (pode ser usado ou removido)
│   ├── styles/             # Arquivos SCSS para estilização
│   │   ├── _override.scss  # Sobrescritas de estilos do Vuetify/outras libs
│   │   ├── _variables.scss # Variáveis SCSS globais (cores, espaçamentos)
│   │   ├── components/     # Estilos específicos por componente Vuetify
│   │   ├── layout/         # Estilos para partes do layout (sidebar, topbar)
│   │   ├── pages/          # Estilos específicos por página/view
│   │   └── style.scss      # Arquivo SCSS principal que importa os outros
│   ├── themes/             # Definições de temas do Vuetify
│   │   └── LightTheme.ts   # Exemplo de definição de tema claro
│   ├── types/              # Definições de tipos TypeScript customizadas
│   │   └── theme/
│   │       └── theme.d.ts  # Tipos relacionados ao tema
│   ├── views/              # Componentes de página (associados às rotas)
│   │   ├── About.vue
│   │   ├── Home.vue
│   │   └── auth/           # Views específicas de autenticação
│   │       └── BoxedLogin.vue
│   └── vite-env.d.ts       # Tipos para variáveis de ambiente injetadas pelo Vite
├── tsconfig.app.json       # Configuração TypeScript específica para o código da app em `src`
├── tsconfig.json           # Configuração TypeScript principal
├── tsconfig.node.json      # Configuração TypeScript para ambientes Node.js (ex: config do Vite)
└── vite.config.ts          # Arquivo de configuração do Vite
```
