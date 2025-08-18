#!/bin/bash
set -e

echo "🚀 Iniciando Apache Laravel..."

# Verificar arquivos essenciais
if [ ! -f .env ]; then
    echo "❌ Arquivo .env não encontrado!"
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "✅ .env criado a partir do .env.example"
    fi
fi

# Criar diretórios se não existirem
mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache

# Definir permissões corretas
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Gerar APP_KEY se necessário
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate --force
fi

# Aguardar MySQL (timeout 30s)
if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "🔌 Aguardando MySQL..."
    timeout 30 bash -c 'until nc -z db 3306; do sleep 1; done' || echo "⚠️  MySQL timeout, continuando..."
fi

# Executar setup Laravel
if [ -f artisan ]; then
    echo "💾 Executando migrações..."
    php artisan migrate --force || echo "⚠️  Migrações falharam"
    
    echo "🔗 Criando link storage..."
    php artisan storage:link || echo "⚠️  Link storage falhou"
    
    echo "🧹 Limpando caches..."
    php artisan config:clear || echo "⚠️  Config clear falhou"
    php artisan cache:clear || echo "⚠️  Cache clear falhou"
fi

# Debug: Verificar configuração
echo "📊 Informações de debug:"
echo "- PHP Version: $(php --version | head -n1)"
echo "- Laravel Version: $(php artisan --version)"
echo "- APP_KEY exists: $(grep -c "APP_KEY=base64:" .env || echo "0")"
echo "- Storage permissions: $(ls -la storage/ | head -3)"

echo "✅ Laravel configurado!"
echo "🌐 Iniciando Apache..."

# Iniciar Apache
exec apache2-foreground