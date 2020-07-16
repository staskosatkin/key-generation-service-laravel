FROM php:7.4-fpm
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql
#    && pecl install xdebug-2.9.6 \
#    && docker-php-ext-enable xdebug

CMD ["php-fpm"]
