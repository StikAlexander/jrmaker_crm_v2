# Usamos una imagen base de PHP optimizada para Laravel.
FROM elrincondeisma/php-for-laravel:8.3.7

# Establecemos el directorio de trabajo
WORKDIR /app

# Copiamos todos los archivos del proyecto (incluido artisan) antes de instalar las dependencias
COPY . .

# Instalar dependencias PHP con Composer
RUN composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-exif

# Instalar nano, dependencias PHP, nodejs y npm (para Alpine Linux o cualquier sistema base de tu imagen)
RUN apk --no-cache update && \
    apk add nano nodejs npm && \
    docker-php-ext-install exif && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install sockets && \
    docker-php-ext-install zip && \
    pecl install redis && docker-php-ext-enable redis

# Instalar dependencias npm y compilar los assets
RUN npm install && npm run build

# Publicar configuración de Octane y otros recursos necesarios
RUN php artisan vendor:publish --tag=octane-config && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan storage:link && \
    php artisan icons:cache

# Instalar y configurar Octane
RUN composer require laravel/octane && \
    php artisan octane:install --server=swoole

# Exponer el puerto para Octane
EXPOSE 8002

# Comando para iniciar Laravel Octane
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8002"]
