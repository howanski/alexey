FROM dockerwest/php-symfony:8.0
COPY prepare-command.sh /prepare-command.sh
COPY sources.list /etc/apt/sources.list
RUN apt-get --allow-releaseinfo-change update > /dev/null
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update > /dev/null && apt-get install -y rsync mariadb-client procps net-tools acl nodejs yarn supervisor iputils-ping wakeonlan > /dev/null
ENV PHP_IDE_CONFIG "serverName=application"
COPY xdebug.ini /etc/php/8.0/mods-available/xdebug.ini
COPY supervisor-alexey-machines-ping.conf /etc/supervisor/conf.d/supervisor-alexey-machines-ping.conf
COPY supervisor-alexey-network-usage-record.conf /etc/supervisor/conf.d/supervisor-alexey-network-usage-record.conf
WORKDIR /var/www/html
