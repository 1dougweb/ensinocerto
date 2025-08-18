#!/bin/bash

echo "🚀 Iniciando configuração da aplicação..."

# Criar diretórios necessários
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions  
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Definir permissões
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Criar .env se não existir
if [ ! -f .env ]; then
    echo "📝 Criando arquivo .env..."
    cp .env.example .env
fi

# Aguardar MySQL estar disponível
echo "🔌 Aguardando MySQL..."
until nc -z db 3306; do
    echo "⏳ Aguardando banco de dados..."
    sleep 2
done
echo "✅ MySQL disponível!"

# Gerar APP_KEY se não existir
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Executar migrações
echo "💾 Executando migrações..."
php artisan migrate --force

# Limpar caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Criar link simbólico para storage
echo "🔗 Criando link do storage..."
php artisan storage:link

echo "✅ Configuração concluída!"

# Iniciar Apache
exec apache2-foreground
