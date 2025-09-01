# Configuração do Google Analytics com Dados Reais

Este documento explica como configurar o Google Analytics no sistema para obter dados reais (não mockados).

## 🚀 **TUTORIAL COMPLETO: Como Conseguir Todas as Chaves**

### **📋 Lista de Chaves Necessárias:**
1. **GOOGLE_ANALYTICS_VIEW_ID** - ID da visualização
2. **GOOGLE_ANALYTICS_PROPERTY_ID** - ID da propriedade  
3. **GOOGLE_ANALYTICS_MEASUREMENT_ID** - ID de medição
4. **GOOGLE_ANALYTICS_CLIENT_ID** - ID do cliente OAuth
5. **GOOGLE_ANALYTICS_CLIENT_SECRET** - Chave secreta OAuth
6. **GOOGLE_ANALYTICS_ACCESS_TOKEN** - Token de acesso (gerado automaticamente)
7. **GOOGLE_ANALYTICS_REFRESH_TOKEN** - Token de renovação (gerado automaticamente)

---

## **🔑 PASSO 1: Configurar Google Analytics**

### **1.1 Criar Conta no Google Analytics**
1. Acesse [Google Analytics](https://analytics.google.com/)
2. Clique em **"Começar a medir"**
3. Preencha as informações da conta:
   - **Nome da conta**: Nome da sua empresa/organização
   - **Nome da propriedade**: Nome do seu site
   - **Fuso horário**: Selecione seu fuso
   - **Moeda**: BRL (Real brasileiro)
4. Clique em **"Próximo"**

### **1.2 Configurar Propriedade**
1. **Informações da empresa**:
   - Tamanho da empresa
   - Como pretende usar o Google Analytics
2. **Objetivos de negócio**: Selecione os que se aplicam
3. Clique em **"Criar"**

### **1.3 Obter Property ID e Measurement ID**
1. Após criar, você verá uma tela com:
   - **Property ID**: `123456789` (anote este número!)
   - **Measurement ID**: `G-XXXXXXXXXX` (anote este código!)
2. Clique em **"Ver dados"**

### **1.4 Obter View ID (GA4)**
1. No painel, vá em **Admin** (⚙️) → **Data Streams**
2. Clique no stream da web
3. **Measurement ID**: `G-XXXXXXXXXX` (mesmo do passo anterior)
4. **Stream ID**: `123456789` (mesmo do Property ID)

---

## **🔑 PASSO 2: Configurar Google Cloud Console**

### **2.1 Criar Projeto**
1. Acesse [Google Cloud Console](https://console.developers.google.com/)
2. Clique em **"Selecionar projeto"** → **"Novo projeto"**
3. **Nome do projeto**: `Analytics Dashboard` (ou nome de sua preferência)
4. Clique em **"Criar"**

### **2.2 Ativar APIs**
1. No menu lateral, vá em **"APIs e serviços"** → **"Biblioteca"**
2. Pesquise e ative as seguintes APIs:
   - **Google Analytics Data API (GA4)** - Clique em **"Ativar"**
   - **Google Analytics API** - Clique em **"Ativar"**
   - **Google Ads API** (se usar Google Ads) - Clique em **"Ativar"**

### **2.3 Configurar Tela de Consentimento OAuth**
1. **"APIs e serviços"** → **"Tela de consentimento OAuth"**
2. **Tipo de usuário**: `Externo`
3. Clique em **"Criar"**
4. Preencha as informações:
   - **Nome do app**: `Analytics Dashboard`
   - **Email de suporte**: Seu email
   - **Email de contato do desenvolvedor**: Seu email
5. Clique em **"Salvar e continuar"**
6. **Escopos**: Clique em **"Salvar e continuar"**
7. **Usuários de teste**: Adicione seu email
8. Clique em **"Salvar e continuar"**

### **2.4 Criar Credenciais OAuth 2.0**
1. **"APIs e serviços"** → **"Credenciais"**
2. Clique em **"Criar credenciais"** → **"ID do cliente OAuth 2.0"**
3. **Tipo de aplicativo**: `Aplicativo para computador`
4. **Nome**: `Analytics Dashboard`
5. Clique em **"Criar"**
6. **Anote o Client ID e Client Secret!**

### **2.5 Configurar URLs de Redirecionamento**
1. Clique na credencial criada para editar
2. **URIs de redirecionamento autorizados**:
   - Adicione: `urn:ietf:wg:oauth:2.0:oob`
3. Clique em **"Salvar"**

---

## **🔑 PASSO 3: Configurar Google Ads (Opcional)**

### **3.1 Obter Developer Token**
1. Acesse [Google Ads](https://ads.google.com/)
2. Faça login na sua conta
3. Vá em **"Ferramentas"** → **"API Center"**
4. Clique em **"Aplicar para acesso à API"**
5. Preencha o formulário
6. Aguarde aprovação (pode levar alguns dias)
7. Após aprovado, copie o **Developer Token**

---

## **🔑 PASSO 4: Configurar no Sistema**

### **4.1 Usar Comandos Automáticos (Recomendado)**
```bash
# Configurar credenciais básicas
php artisan analytics:setup

# O comando vai pedir:
# - Client ID
# - Client Secret  
# - Property ID
# - Measurement ID

# Depois autenticar
php artisan analytics:auth
```

### **4.2 Configuração Manual no .env**
```env
# Google Analytics Configuration
GOOGLE_ANALYTICS_VIEW_ID=123456789
GOOGLE_ANALYTICS_PROPERTY_ID=123456789
GOOGLE_ANALYTICS_MEASUREMENT_ID=G-XXXXXXXXXX

# OAuth 2.0 Credentials
GOOGLE_ANALYTICS_CLIENT_ID=seu_client_id_aqui
GOOGLE_ANALYTICS_CLIENT_SECRET=seu_client_secret_aqui

# Google Ads API (opcional)
GOOGLE_ADS_DEVELOPER_TOKEN=seu_developer_token_aqui
```

---

## **🔑 PASSO 5: Autenticação OAuth**

### **5.1 Executar Comando de Autenticação**
```bash
php artisan analytics:auth
```

### **5.2 Processo de Autenticação**
1. O comando vai gerar uma URL
2. **Copie e cole a URL no navegador**
3. Faça login na sua conta Google
4. **Autorize o acesso** quando solicitado
5. **Copie o código de autorização**
6. Cole o código no terminal
7. O sistema vai gerar automaticamente:
   - `GOOGLE_ANALYTICS_ACCESS_TOKEN`
   - `GOOGLE_ANALYTICS_REFRESH_TOKEN`

---

## **📱 Resumo das Chaves:**

| Chave | Onde Conseguir | Formato |
|-------|----------------|---------|
| **VIEW_ID** | Google Analytics → Admin → View Settings | `123456789` |
| **PROPERTY_ID** | Google Analytics → Admin → Property Settings | `123456789` |
| **MEASUREMENT_ID** | Google Analytics → Data Streams | `G-XXXXXXXXXX` |
| **CLIENT_ID** | Google Cloud Console → Credenciais OAuth | `123456789-abcdef.apps.googleusercontent.com` |
| **CLIENT_SECRET** | Google Cloud Console → Credenciais OAuth | `GOCSPX-abcdefghijklmnop` |
| **ACCESS_TOKEN** | Comando `analytics:auth` | Gerado automaticamente |
| **REFRESH_TOKEN** | Comando `analytics:auth` | Gerado automaticamente |

---

## **🚨 Problemas Comuns:**

### **Erro: "API não ativada"**
- Verifique se ativou as APIs no Google Cloud Console
- Aguarde alguns minutos após ativar

### **Erro: "Credenciais inválidas"**
- Verifique se copiou corretamente Client ID e Secret
- Confirme se configurou a tela de consentimento OAuth

### **Erro: "Acesso negado"**
- Verifique se adicionou seu email como usuário de teste
- Confirme se autorizou o acesso no Google

### **Erro: "Quota excedida"**
- As APIs gratuitas têm limites diários
- Aguarde até o próximo dia ou solicite aumento de quota

---

## **✅ Verificação Final:**

1. **Execute**: `php artisan analytics:setup`
2. **Execute**: `php artisan analytics:auth`
3. **Acesse**: `/dashboard/analytics`
4. **Verifique**: Se os gráficos carregam com dados reais

---

## Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Google Analytics Configuration
GOOGLE_ANALYTICS_VIEW_ID=your_view_id_here
GOOGLE_ANALYTICS_PROPERTY_ID=your_property_id_here
GOOGLE_ANALYTICS_MEASUREMENT_ID=G-XXXXXXXXXX
GOOGLE_TAG_MANAGER_ID=GTM-XXXXXXX

# OAuth 2.0 Credentials (obrigatório para dados reais)
GOOGLE_ANALYTICS_CLIENT_ID=your_client_id_here
GOOGLE_ANALYTICS_CLIENT_SECRET=your_client_secret_here
GOOGLE_ANALYTICS_ACCESS_TOKEN=your_access_token_here
GOOGLE_ANALYTICS_REFRESH_TOKEN=your_refresh_token_here

# Google Ads API (opcional)
GOOGLE_ADS_DEVELOPER_TOKEN=your_developer_token_here
```

## Configuração do Google Analytics

## 🚀 Configuração Automática (Recomendado)

### 1. Usar Comandos Artisan

O sistema possui comandos automatizados para facilitar a configuração:

```bash
# Configurar credenciais básicas
php artisan analytics:setup

# Autenticar e obter tokens
php artisan analytics:auth
```

### 2. Configuração Manual

Se preferir configurar manualmente:

#### 1. Criar Conta no Google Analytics

1. Acesse [Google Analytics](https://analytics.google.com/)
2. Crie uma nova conta ou use uma existente
3. Crie uma nova propriedade para seu site
4. Anote o **Property ID** (formato: 123456789)

#### 2. Configurar Visualização (View)

1. Na propriedade criada, crie uma nova visualização
2. Anote o **View ID** (formato: 123456789)

### 3. Configurar Google Tag Manager (Opcional)

1. Acesse [Google Tag Manager](https://tagmanager.google.com/)
2. Crie um novo container
3. Anote o **Container ID** (formato: GTM-XXXXXXX)

### 4. Configurar Google Ads (Para Campanhas)

1. Acesse [Google Ads](https://ads.google.com/)
2. Conecte sua conta do Google Analytics
3. Configure o rastreamento de conversões

## Configuração da API

### 1. Criar Projeto no Google Cloud Console

1. Acesse [Google Cloud Console](https://console.developers.google.com/)
2. Crie um novo projeto ou selecione um existente
3. Ative as seguintes APIs:
   - Google Analytics Data API (GA4)
   - Google Analytics API
   - Google Ads API (se usar Google Ads)
4. Configure as credenciais OAuth 2.0

#### 5. Configurar Credenciais OAuth 2.0

1. No Google Cloud Console, vá em "APIs & Services" > "Credentials"
2. Clique em "Create Credentials" > "OAuth 2.0 Client IDs"
3. Selecione "Desktop application" como tipo
4. Dê um nome à credencial (ex: "Analytics Dashboard")
5. Clique em "Create"
6. Anote o **Client ID** e **Client Secret**

#### 6. Configurar URLs de Redirecionamento

1. Na credencial OAuth criada, clique em "Edit"
2. Em "Authorized redirect URIs", adicione:
   - `urn:ietf:wg:oauth:2.0:oob` (para autenticação via console)
3. Clique em "Save"

## Permissões no Sistema

### 1. Criar Permissão

Execute o comando para criar a permissão de analytics:

```bash
php artisan permission:create-permission analytics.view
```

### 2. Atribuir Permissão

Atribua a permissão aos usuários que devem acessar o dashboard de analytics.

## Estrutura de Arquivos

```
app/
├── Http/Controllers/
│   └── GoogleAnalyticsController.php
├── Services/
│   └── GoogleAnalyticsService.php ✅ IMPLEMENTADO
├── Console/Commands/
│   ├── SetupGoogleAnalytics.php ✅ NOVO
│   └── GoogleAnalyticsAuth.php ✅ NOVO
resources/
└── views/
    └── admin/
        └── analytics/
            └── dashboard.blade.php
config/
└── services.php ✅ ATUALIZADO
```

## Funcionalidades Implementadas

### Dashboard de Analytics

- **Métricas Principais**: Sessões, usuários, visualizações, taxa de rejeição
- **Métricas do Google Ads**: Cliques, impressões, custo, conversões
- **Gráficos Interativos**: 
  - Sessões ao longo do tempo
  - Fontes de tráfego
  - Performance do Google Ads
  - Top campanhas
- **Filtros de Data**: 7 dias, 30 dias, 3 meses, 1 ano
- **Tabela de Dados**: Dados detalhados por dia

### APIs Disponíveis

- `GET /dashboard/analytics` - Dashboard principal
- `GET /dashboard/analytics/data` - Dados do Google Analytics
- `GET /dashboard/analytics/ads-data` - Dados do Google Ads
- `GET /dashboard/analytics/traffic-sources` - Fontes de tráfego

## ✅ Dados Reais Implementados

O sistema agora usa dados reais do Google Analytics e Google Ads! 

### Como Funciona

1. **Integração Real**: Conecta diretamente com as APIs do Google
2. **Fallback Inteligente**: Se não conseguir dados reais, usa dados mockados
3. **Cache Otimizado**: Dados são cacheados por 15 minutos para performance
4. **OAuth 2.0**: Autenticação segura via tokens de acesso

### APIs Utilizadas

- **Google Analytics Data API (GA4)**: Para métricas de tráfego
- **Google Ads API**: Para dados de campanhas e anúncios
- **OAuth 2.0**: Para autenticação segura

## 🎯 Próximos Passos

1. **✅ Integração Real**: Já implementada com APIs do Google
2. **📊 Relatórios**: Adicionar relatórios exportáveis (PDF, Excel)
3. **🔔 Alertas**: Configurar alertas para métricas importantes
4. **📱 Mobile**: Otimizar dashboard para dispositivos móveis
5. **🔄 Sincronização**: Implementar sincronização automática de dados
5. **Segmentação**: Adicionar segmentação avançada de usuários

## Troubleshooting

### Erro de Permissão

Se receber erro de permissão, verifique:
- Se a permissão `analytics.view` foi criada
- Se o usuário tem a permissão atribuída
- Se está logado como admin

### Erro de API

Se receber erro de API do Google:
- Verifique se as APIs estão ativadas no Google Cloud Console
- Confirme se a conta de serviço tem as permissões corretas
- Verifique se o arquivo de chave está no local correto

### Gráficos Não Carregam

Se os gráficos não carregarem:
- Verifique se o Chart.js está sendo carregado
- Abra o console do navegador para ver erros JavaScript
- Confirme se as rotas estão funcionando

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `storage/logs/laravel.log`
2. Consulte a documentação oficial do Google Analytics
3. Abra uma issue no repositório do projeto
