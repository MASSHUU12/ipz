FROM php:8.3-apache as web

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    openssl

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -fsSL https://bun.sh/install | bash

COPY . /app

RUN composer install
# RUN bun source /root/.bashrc
# RUN bun install

RUN openssl genpkey -algorithm RSA -out /etc/ssl/private/private_key.pem -aes256 -passout pass:Zaq12wsx
RUN openssl rsa -pubout -in /etc/ssl/private/private_key.pem -out /etc/ssl/private/public_key.pem -passin pass:Zaq12wsx

RUN php artisan config:clear
# RUN php artisan cache:clear

EXPOSE 8000

# CMD php artisan serve --host=0.0.0.0 --port=8000
