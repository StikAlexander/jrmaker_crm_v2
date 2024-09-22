FROM elrincondeisma/php-for-laravel:8.3.7

WORKDIR /app
COPY . .

# Instalar dependencias PHP y npm ademas editor nano
RUN apt-get update && apt-get install -y nano && \
    docker-php-ext-install exif && \
    composer install --optimize-autoloader --no-dev && \
    composer require laravel/octane && \
    npm install && npm run build

# Instalar y configurar Octane
RUN php artisan octane:install --server=swoole

# Exponer el puerto
EXPOSE 8001

# Comando para iniciar Laravel Octane
CMD php artisan octane:start --server=swoole --host=0.0.0.0 --port=8001
