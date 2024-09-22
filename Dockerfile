# Este es un comentario para provocar un build.

FROM elrincondeisma/php-for-laravel:8.3.7

# Establece el directorio de trabajo
WORKDIR /app

# Copiar el contenido de la aplicación al contenedor
COPY . .

# Instalar dependencias de Composer y npm
RUN docker-php-ext-install exif && \
    composer install --optimize-autoloader --no-dev && \
    npm install && npm run build

# Crear directorios necesarios y ajustar permisos para logs, caché y almacenamiento
RUN mkdir -p /app/storage/logs /app/storage/framework/cache/data /app/storage/framework/sessions /app/storage/framework/views /app/bootstrap/cache && \
    chmod -R 777 /app/storage /app/bootstrap/cache /app/public

# Exponer el puerto en el que Octane se ejecutará
EXPOSE 8001

# Comando de inicio de Laravel Octane con Swoole
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8001"]
