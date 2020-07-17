FROM php:7.4.8-cli
RUN apt-get update && apt-get install -y supervisor libpq-dev \
   && docker-php-ext-install pdo pdo_pgsql pgsql

RUN apt-get install -y python3 python3-pip

COPY docker/supervisor/my-file.conf /etc/supervisor/conf.d/

CMD ["/usr/bin/supervisord", "--nodaemon"]
