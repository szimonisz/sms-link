FROM debian:latest
RUN apt update
RUN apt -y install php apache2 zip unzip php-curl php-ssh2

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY sms-link.conf /etc/apache2/sites-available
COPY php.ini /etc/php/8.2/apache2/conf.d/custom.ini

RUN a2ensite sms-link.conf

CMD chown -R www-data:www-data /keys && apache2ctl -D FOREGROUND
