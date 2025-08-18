# 🚀 Deploy no EasyPanel

## Pré-requisitos

1. Conta no EasyPanel
2. Repositório Git (GitHub, GitLab, etc.)
3. Arquivos do projeto preparados

## Arquivos Necessários

- ✅ `Dockerfile` - Configuração do container
- ✅ `docker-compose.yml` - Orquestração dos serviços
- ✅ `docker/apache.conf` - Configuração do Apache
- ✅ `.dockerignore` - Arquivos a ignorar no build
- ✅ `setup-easypanel.sh` - Script de configuração

## Passos para Deploy

### 1. Subir código para Git
```bash
git add .
git commit -m "Preparar deploy para EasyPanel"
git push origin main
```

### 2. No EasyPanel

1. **Criar novo projeto**
2. **Conectar repositório Git**
3. **Configurar variáveis de ambiente:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:SUA_CHAVE_AQUI
   DB_CONNECTION=sqlite
   DB_DATABASE=/var/www/html/database/database.sqlite
   ```

4. **Configurar portas:**
   - Porta interna: `80`
   - Porta externa: `80` ou `443`

### 3. Variáveis de Ambiente Importantes

```env
# App
APP_NAME="Ensino Certo"
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

# Evolution API WhatsApp
EVOLUTION_API_BASE_URL=https://evolutionapi.autotxt.online
EVOLUTION_API_KEY=sua_api_key_aqui
EVOLUTION_API_INSTANCE=ensino-certo

# Mail
MAIL_MAILER=smtp
MAIL_HOST=seu_smtp
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
```

### 4. Após Deploy

Execute comandos no terminal do EasyPanel:
```bash
./setup-easypanel.sh
```

## Vantagens do EasyPanel

- ✅ **Conectividade livre** - Sem bloqueios de API
- ✅ **Docker nativo** - Ambiente isolado
- ✅ **SSL automático** - HTTPS grátis
- ✅ **Git integration** - Deploy automático
- ✅ **Logs em tempo real** - Debug fácil
- ✅ **Backup automático** - Dados seguros

## Testando a Integração WhatsApp

Após o deploy, teste:

1. **Diagnóstico:**
   ```bash
   php artisan whatsapp:diagnostic
   ```

2. **Teste de criação:**
   ```bash
   php artisan whatsapp:test-create
   ```

3. **Interface web:**
   - Acesse `/admin/whatsapp`
   - Clique em "Criar Instância"
   - Verifique QR Code

## Troubleshooting

### Se der erro de permissão:
```bash
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
```

### Se der erro de APP_KEY:
```bash
php artisan key:generate
```

### Para ver logs:
```bash
tail -f /var/www/html/storage/logs/laravel.log
```

## 🎯 Resultado Esperado

Com EasyPanel, a Evolution API deve funcionar perfeitamente:
- ✅ Criação de instância
- ✅ Geração de QR Code  
- ✅ Conectividade total
- ✅ Sem timeouts
