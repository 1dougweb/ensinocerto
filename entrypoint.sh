#!/bin/bash
set -e

echo "🚀 Iniciando Ensino Certo..."

# Aguardar MySQL se disponível (não bloquear se não houver)
if [ "${DB_CONNECTION:-}" = "mysql" ] && [ "${DB_HOST:-}" = "db" ]; then
    echo "🔌 Aguardando MySQL..."
    timeout 30 bash -c 'until nc -z db 3306; do sleep 1; done' || echo "⚠️  MySQL não disponível, continuando..."
fi

# Executar migrações se possível
if [ -f artisan ]; then
    echo "💾 Executando migrações..."
    php artisan migrate --force || echo "⚠️  Migrações falharam, continuando..."
    
    echo "🔗 Criando link storage..."
    php artisan storage:link || echo "⚠️  Link storage falhou, continuando..."
fi

echo "✅ Configuração concluída!"
echo "🌐 Iniciando Apache..."

# Iniciar Apache
exec apache2-foreground