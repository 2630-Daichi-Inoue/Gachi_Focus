FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
 && docker-php-ext-install pdo pdo_mysql zip

COPY . .

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php \
 && php composer.phar install --no-dev --optimize-autoloader

# 👇 ここ追加
RUN php artisan config:clear

CMD php artisan serve --host 0.0.0.0 --port $PORT
