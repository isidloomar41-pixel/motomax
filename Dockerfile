FROM php:8.2-apache

# Instalar extensiones de PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Copiar archivos del proyecto
COPY . /var/www/html/

# Cambiar permisos
RUN chown -R www-data:www-data /var/www/html/

# Puerto
EXPOSE 80
