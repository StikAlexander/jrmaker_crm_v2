FROM elrincondeisma/php-for-laravel:8.3.7

WORKDIR /app
COPY . .

# Instalar dependencias PHP y npm
RUN docker-php-ext-install exif && \
    composer install --optimize-autoloader --no-dev && \
    composer require laravel/octane && \
    npm install && npm run build

# Crear directorios de caché y asignar permisos
RUN mkdir -p /app/storage/framework/cache/data && \
    mkdir -p /app/storage/framework/sessions && \
    mkdir -p /app/storage/framework/views && \
    mkdir -p /app/bootstrap/cache && \
    chmod -R 777 /app/storage /app/bootstrap/cache

# Instalar y configurar Octane
RUN php artisan octane:install --server=swoole

# Exponer el puerto
EXPOSE 8001

# Comando para iniciar Laravel Octane
CMD php artisan octane:start --server=swoole --host=0.0.0.0 --port=8001
