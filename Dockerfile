FROM elrincondeisma/php-for-laravel:8.3.7

WORKDIR /app
COPY . .

RUN docker-php-ext-install exif && \
    composer install --optimize-autoloader --no-dev && \
    composer require laravel/octane && \
    mkdir -p /app/storage/logs

RUN apk update && \
    apk add --no-cache nano curl nodejs npm git && \
    npm install && npm run build

RUN php artisan octane:install --server=swoole

EXPOSE 8001

CMD php artisan octane:start --server=swoole --host=0.0.0.0 --port=8001
