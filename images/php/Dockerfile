FROM php:7.3-fpm as php_bothelp

RUN apt-get update && apt-get install -y \
            libfreetype6-dev \
            libpq-dev \
            libmemcached-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
            libmagickwand-dev \
            libmcrypt-dev \
            libicu-dev \
            libzip-dev \
            zip \
            libxslt1-dev \
            libcurl4-gnutls-dev \
            poppler-utils \
            supervisor \
            pkg-config \
            libssl-dev \
            cron

RUN docker-php-source extract \
    && docker-php-ext-install -j$(nproc) gd \
        bcmath \
        bz2 \
        calendar \
        exif \
        gettext \
        mysqli \
        opcache \
        pdo_mysql \
        soap \
        sockets \
        xmlrpc \
        xsl \
        zip \
        curl \
    && docker-php-ext-install intl && docker-php-ext-enable intl \
    && docker-php-source delete

RUN docker-php-ext-install pcntl && docker-php-ext-enable pcntl

RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN chown -R www-data:www-data /var/www

WORKDIR /var/www/bothelp
