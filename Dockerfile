# ============================================================
#  Imagen para Railway: PHP 8.2 + Apache + MySQL
#  Railway detecta este archivo solo y lo usa para el deploy.
# ============================================================
FROM php:8.2-apache

# Driver de MySQL para PHP
RUN docker-php-ext-install pdo_mysql

# Railway asigna el puerto en la variable $PORT (por defecto 8080).
# Hacemos que Apache escuche en ese puerto.
ENV PORT=8080
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# Copiamos todo el sitio
COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 8080
CMD ["apache2-foreground"]
