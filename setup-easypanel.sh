#!/bin/bash

echo "🚀 Configurando aplicação para EasyPanel..."

# Gerar APP_KEY se não existir
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Rodar migrações
php artisan migrate --force

# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar link simbólico para storage
php artisan storage:link

echo "✅ Setup concluído!"
