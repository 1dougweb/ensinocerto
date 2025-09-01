# Google Tag Manager na Landing Page

## Visão Geral

Esta funcionalidade permite configurar o Google Tag Manager (GTM) especificamente para a landing page, independentemente das configurações gerais de tracking do sistema.

## Configuração

### 1. Acessar Configurações

1. Faça login no painel administrativo
2. Navegue para **Configurações** → **Tracking**
3. Role até a seção **Google Tag Manager - Landing Page**

### 2. Configurar GTM

#### Ativar GTM na Landing Page
- **Campo**: Checkbox "Ativar Google Tag Manager"
- **Descrição**: Habilita o GTM especificamente na landing page
- **Padrão**: Desativado

#### ID do GTM
- **Campo**: Input de texto
- **Formato**: GTM-XXXXXXX (ex: GTM-NPXJKW38)
- **Descrição**: ID único do seu container do Google Tag Manager
- **Obrigatório**: Sim (quando ativado)

#### Eventos Personalizados
- **Campo**: Área de texto
- **Descrição**: Configurações adicionais de eventos para o GTM
- **Obrigatório**: Não
- **Uso**: Para configurações avançadas de tracking

### 3. Salvar Configurações

1. Preencha os campos necessários
2. Clique em **Salvar Alterações**
3. As configurações são aplicadas automaticamente

## Implementação Técnica

### Estrutura das Tags

#### Tag JavaScript (Head)
```html
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
<!-- End Google Tag Manager -->
```

#### Tag Noscript (Body)
```html
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
```

### Condições de Exibição

As tags do GTM só são exibidas quando:
1. `landing_gtm_enabled` = `true`
2. `landing_gtm_id` não está vazio
3. O usuário está na landing page (`welcome.blade.php`)

### ⚠️ **Lógica de Prioridade**

Para evitar conflitos, o sistema implementa uma **lógica de prioridade**:

- **Se GTM da Landing Page estiver ativo**: 
  - ✅ Carrega APENAS o GTM específico da landing page
  - ❌ **NÃO** carrega o GTM geral do sistema
  - 🔧 Cria um `dataLayer` simulado para compatibilidade

- **Se GTM da Landing Page estiver inativo**:
  - ✅ Carrega o GTM geral do sistema (via `tracking-scripts`)
  - ❌ **NÃO** carrega o GTM específico da landing page

**Resultado**: Nunca há duplicação de GTM na página!

## Arquivos Modificados

### Backend
- `app/Http/Controllers/SettingsController.php` - Processamento das configurações
- `app/Models/SystemSetting.php` - Método getLandingPageSettings()
- `database/seeders/LandingGtmSettingsSeeder.php` - Seeder para configurações iniciais

### Frontend
- `resources/views/admin/settings/index.blade.php` - Interface de configuração (aba Tracking)
- `resources/views/welcome.blade.php` - Implementação das tags GTM

## Banco de Dados

### Tabela: system_settings

| Chave | Tipo | Categoria | Descrição |
|-------|------|-----------|-----------|
| `landing_gtm_enabled` | boolean | landing_page | Ativar GTM na landing page |
| `landing_gtm_id` | string | landing_page | ID do Google Tag Manager |
| `landing_gtm_events` | text | landing_page | Eventos personalizados do GTM |

## Exemplo de Uso

### 1. Configuração Básica
```php
// Ativar GTM
landing_gtm_enabled: true

// Definir ID
landing_gtm_id: GTM-NPXJKW38
```

### 2. Verificar Configurações
```php
$landingSettings = \App\Models\SystemSetting::getLandingPageSettings();

if ($landingSettings['gtm_enabled'] && !empty($landingSettings['gtm_id'])) {
    // GTM está ativo e configurado
    $gtmId = $landingSettings['gtm_id'];
}
```

## Vantagens

### ✅ **Independência**
- Configurações separadas das configurações gerais de tracking
- Não interfere com outras implementações de GTM

### ✅ **Flexibilidade**
- Pode ser ativado/desativado independentemente
- Suporte a eventos personalizados

### ✅ **Segurança**
- Validação de campos obrigatórios
- Sanitização de dados de entrada

### ✅ **Manutenibilidade**
- Código limpo e organizado
- Fácil de modificar e expandir

## Troubleshooting

### GTM não aparece na página
1. Verifique se `landing_gtm_enabled` está ativo
2. Confirme se `landing_gtm_id` está preenchido
3. Limpe o cache do navegador
4. Verifique os logs do Laravel

### ⚠️ **Conflitos de GTM Duplicado**
**Sintoma**: Múltiplas tags GTM na página ou erros de JavaScript
**Solução**: 
1. Verificar se o GTM da landing page está ativo
2. Confirmar que apenas UM GTM está sendo carregado
3. Usar a lógica de prioridade implementada

**Prevenção**: O sistema automaticamente evita duplicação usando a lógica de prioridade

### Erro de JavaScript
1. Valide o formato do ID do GTM
2. Verifique se o container existe no Google Tag Manager
3. Teste em modo de desenvolvimento

### Configurações não salvam
1. Verifique as permissões do usuário
2. Confirme se todos os campos obrigatórios estão preenchidos
3. Verifique os logs de erro

## Próximos Passos

### Funcionalidades Futuras
- [ ] Suporte a múltiplos containers GTM
- [ ] Configuração de eventos automáticos
- [ ] Integração com Google Analytics 4
- [ ] Relatórios de performance do GTM

### Melhorias Técnicas
- [ ] Cache das configurações
- [ ] Validação em tempo real do ID do GTM
- [ ] Teste de conectividade com o GTM
- [ ] Backup automático das configurações

---

**Versão**: 1.0  
**Última atualização**: Janeiro 2025  
**Desenvolvedor**: Sistema Ensino Certo
