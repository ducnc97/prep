FROM php:8.3.14-fpm

ARG user=appuser
ARG group=appuser
ARG uid=1000
ARG gid=1000
RUN groupadd -g ${gid} ${group} && useradd -m -u ${uid} -g ${group} -s /bin/sh ${user}

# config and install php gd, pdo, pdo mysql
RUN apt-get update -y && apt-get install -y zlib1g-dev libpng-dev unzip
RUN docker-php-ext-configure gd
RUN docker-php-ext-install gd pdo pdo_mysql

# install composer and laravel
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER ${user}
