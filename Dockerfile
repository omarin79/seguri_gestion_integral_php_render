FROM php:8.2-apache

# Actualiza la lista de paquetes e instala las librerías de desarrollo de PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

# Instala las extensiones de PHP necesarias (PDO y el driver PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql

# Copia los archivos de tu proyecto al directorio web del servidor
COPY . /var/www/html/

# Establece los permisos correctos y habilita mod_rewrite para URLs amigables
RUN chown -R www-data:www-data /var/www/html && \
    a2enmod rewrite