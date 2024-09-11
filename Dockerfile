FROM elrincondeisma/php-for-laravel:8.3.7

WORKDIR /app
COPY . .

# Instala la extensión exif antes de ejecutar composer install
RUN docker-php-ext-install exif

RUN composer install
RUN composer require laravel/octane
RUN mkdir -p /app/storage/logs

# Establecer variables de entorno para MySQL
ENV DB_CONNECTION=mysql
ENV DB_HOST=mysql_db
ENV DB_PORT=3306
ENV DB_DATABASE=jr_maker_sas
ENV DB_USERNAME=root
ENV DB_PASSWORD=ae031323

RUN php artisan octane:install --server="swoole"

CMD php artisan octane:start --server="swoole" --host="0.0.0.0"
EXPOSE 8000
