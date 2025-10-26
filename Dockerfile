FROM php:8.2-apache

# Instala las extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_pgsql

# Copia los archivos de tu proyecto al directorio web del servidor
COPY . /var/www/html/

# Otorga los permisos adecuados
RUN chown -R www-data:www-data /var/www/html && \
    a2enmod rewrite