# Imagen base optimizada para Laravel y Filament con PHP 8.3.7 y Swoole
FROM elrincondeisma/php-for-laravel:8.3.7

# Establece el directorio de trabajo
WORKDIR /app

# Copiar el contenido de la aplicación al contenedor
COPY . .

# Instalar dependencias de Composer y npm
RUN composer install --optimize-autoloader --no-dev && \
    npm install && npm run build

# Crear directorios necesarios y ajustar permisos para logs y almacenamiento
RUN mkdir -p /app/storage/logs && \
    chmod -R 777 /app/storage /app/public

# Exponer el puerto en el que Octane se ejecutará
EXPOSE 8001

# Comando de inicio de Laravel Octane con Swoole
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8001"]
