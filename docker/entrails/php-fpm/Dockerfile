FROM dockerwest/php-symfony:8.0
COPY prepare-command.sh /prepare-command.sh
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update > /dev/null && apt-get install -y rsync mariadb-client procps net-tools acl nodejs yarn > /dev/null
ENV PHP_IDE_CONFIG "serverName=application"
COPY xdebug.ini /etc/php/8.0/mods-available/xdebug.ini
WORKDIR /var/www/html
