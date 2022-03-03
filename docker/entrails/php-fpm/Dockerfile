FROM dockerwest/php-symfony:8.0
COPY prepare-command.sh /prepare-command.sh
COPY sources.list /etc/apt/sources.list
COPY xdebug.ini /etc/php/8.0/mods-available/xdebug.ini
COPY www.conf /etc/php/8.0/fpm/pool.d/www.conf
ADD supervisor /etc/supervisor/conf.d/
RUN apt-get --allow-releaseinfo-change update > /dev/null
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - > /dev/null
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - > /dev/null
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list > /dev/null
RUN apt-get update > /dev/null && apt-get install -y vim mariadb-client htop net-tools nodejs yarn supervisor iputils-ping wakeonlan > /dev/null
ENV PHP_IDE_CONFIG "serverName=application"
WORKDIR /var/www/html
