# ==============================
# Etapa 1 - Base do PHP + Extensões
# ==============================
FROM php:8.2-fpm AS base

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    nano \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip bcmath

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ==============================
# Etapa 2 - Instalar Node.js
# ==============================
FROM base AS with-node

# Instalar Node.js (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g pnpm yarn

# ==============================
# Etapa 3 - Configuração final
# ==============================
WORKDIR /var/www/html

# Copiar o código do Laravel
COPY . .

# Permissões para o storage e bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expor porta do PHP-FPM
EXPOSE 9000

# Comando padrão
CMD ["php-fpm"]
