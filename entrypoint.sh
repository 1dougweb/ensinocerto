#!/bin/bash
set -e

echo "🚀 Iniciando Apache Laravel..."

# Aguardar MySQL (timeout 60s)
if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "🔌 Aguardando MySQL..."
    timeout 60 bash -c 'until curl -s db:3306 >/dev/null 2>&1; do sleep 2; done' || echo "⚠️  MySQL timeout, continuando..."
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

echo "✅ Laravel configurado!"
echo "🌐 Iniciando Apache..."

# Iniciar Apache
exec apache2-foreground