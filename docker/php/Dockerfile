FROM php:8.2-apache

# Extensiones que usa la aplicación (mysqli para la BD, zlib ya viene incluido para FPDF)
RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers

# Configuración de PHP para producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/app.ini "$PHP_INI_DIR/conf.d/app.ini"

WORKDIR /var/www/html
COPY --chown=www-data:www-data . /var/www/html

EXPOSE 80
