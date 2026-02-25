# 1) Frontend build
FROM node:20 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# 2) PHP runtime
FROM php:8.2-cli
WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
 && docker-php-ext-install pdo pdo_mysql zip

COPY . .

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php \
 && php composer.phar install --no-dev --optimize-autoloader

# Copy built assets
COPY --from=frontend /app/public/build /app/public/build

# Laravel config cache周り（任意だけど安定）
RUN php artisan config:clear

CMD sh -c "php -S 0.0.0.0:${PORT:-8080} -t public"
