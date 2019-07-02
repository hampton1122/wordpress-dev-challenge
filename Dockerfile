FROM php:7.2.11-apache

# Install other needed extensions
# Full list here : https://github.com/docker-library/php/issues/75
RUN apt-get update \
    && apt-get install -y libfreetype6-dev zlib1g-dev libjpeg62-turbo-dev libpng-dev && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mbstring \
    && apt-get install -y mysql-client \
    && docker-php-ext-install mysqli \
    && apt-get install -y curl libcurl3 libcurl3-dev \
    && docker-php-ext-install curl \
    && docker-php-ext-install json \
    && docker-php-ext-install zip \
    && a2enmod rewrite \
    && a2enmod headers \
    && apt-get update -y

RUN apt-get install -y zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip

RUN apt-get update && apt-get install -y libbz2-dev
RUN docker-php-ext-install bz2

#replace php ini file
COPY php.ini /usr/local/etc/php/conf.d/php.ini
WORKDIR /var/www/html
COPY ./wordpress /var/www/html

EXPOSE 80
