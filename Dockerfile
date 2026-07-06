FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork

ENV PORT=8080
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 8080

CMD ["apache2-foreground"]
