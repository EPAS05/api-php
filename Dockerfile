FROM php:8.4-fpm

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get update && apt-get install -y nodejs

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    zip \
    libpq-dev \ 
    && docker-php-ext-install intl pdo mbstring zip pdo_pgsql \ 
    && rm -rf /var/lib/apt/lists/* 
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]