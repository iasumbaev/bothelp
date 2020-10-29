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
    && pecl install imagick && docker-php-ext-enable imagick \
    && pecl install mcrypt && docker-php-ext-enable mcrypt \
    && pecl install mailparse && docker-php-ext-enable mailparse \
    && pecl install redis && docker-php-ext-enable redis \
    && pecl install mongodb && docker-php-ext-enable mongodb \
    && docker-php-source delete

RUN docker-php-ext-install pcntl && docker-php-ext-enable pcntl

RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN chown -R www-data:www-data /var/www

# Production mode as default
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

WORKDIR /var/www


# Editors
RUN apt-get update && apt-get install -y vim

# XDebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
