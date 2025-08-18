FROM php:8.2-apache

# Instalar apenas o essencial
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_mysql gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Copiar código
WORKDIR /var/www/html
COPY . .

# Instalar dependências e build
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Configurar Apache simples
RUN echo 'DocumentRoot /var/www/html/public' > /etc/apache2/sites-available/000-default.conf

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Criar .env básico
RUN echo 'APP_NAME="Ensino Certo"' > .env \
    && echo 'APP_ENV=production' >> .env \
    && echo 'APP_KEY=' >> .env \
    && echo 'APP_DEBUG=true' >> .env \
    && echo 'DB_CONNECTION=mysql' >> .env \
    && echo 'DB_HOST=db' >> .env \
    && echo 'DB_DATABASE=ensino_certo' >> .env \
    && echo 'DB_USERNAME=ensino_certo_user' >> .env \
    && echo 'DB_PASSWORD=senha_segura' >> .env \
    && php artisan key:generate --force

EXPOSE 80
CMD ["apache2-foreground"]