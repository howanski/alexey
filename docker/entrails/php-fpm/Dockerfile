FROM php:8.2-fpm
RUN curl -sL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get update && apt-get install -y unzip libzip-dev vim mariadb-client htop net-tools nodejs supervisor iputils-ping wget libicu-dev libfreetype-dev libjpeg62-turbo-dev libpng-dev redis  --fix-missing
RUN npm install -g corepack
RUN pecl install xdebug-3.2.1 && docker-php-ext-enable xdebug
RUN pecl install apcu && docker-php-ext-enable apcu
RUN docker-php-ext-install sockets && docker-php-ext-install zip && docker-php-ext-install pdo_mysql && docker-php-ext-install opcache && docker-php-ext-install intl && docker-php-ext-install gd
COPY prepare-command.sh /prepare-command.sh
RUN chmod +x /prepare-command.sh
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY www.conf /usr/local/etc/php-fpm.d/
ADD supervisor /etc/supervisor/conf.d/
WORKDIR /var/www/html
CMD ["/prepare-command.sh"]
