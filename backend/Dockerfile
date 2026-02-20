FROM php:8.2-apache
# Install necessary packages and enable Apache modules
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip libsodium-dev \
    && docker-php-ext-install pdo_mysql sodium zip \
    && a2enmod rewrite headers


#Configuració Apache perquè la carpeta publica sigui "public/" de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

#Fix de directori de treball
WORKDIR /var/www/html