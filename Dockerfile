FROM php:8.3-apache

COPY . /var/www/html

RUN docker-php-ext-install sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-interaction --no-plugins --no-scripts

RUN a2enmod rewrite

CMD ["php", "docker_server.php"]
