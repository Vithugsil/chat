FROM php:8.1-cli

RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
      git \
      unzip \
      zlib1g-dev \
      libzip-dev && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json  /app/
RUN composer install --no-dev --optimize-autoloader

COPY src/ /app/src/
COPY public/ /app/public/

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
