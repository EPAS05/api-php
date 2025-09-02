FROM php:8.3-fpm

# Устанавливаем зависимости и расширения
RUN apt-get update && apt-get install -y \
    libicu-dev libonig-dev libzip-dev zip \
    && docker-php-ext-install intl pdo mbstring zip

# Ставим composer внутрь контейнера
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
