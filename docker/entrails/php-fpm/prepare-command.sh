#!/bin/bash
cd /var/www/html
rm -rf var/log
rm -rf var/cache
rm -rf public/build
wget -c https://getcomposer.org/download/2.8.2/composer.phar
XDEBUG_MODE=off php composer.phar install
XDEBUG_MODE=off yarn install
XDEBUG_MODE=off yarn build

until XDEBUG_MODE=off php bin/console dbal:run-sql -q "show tables"; do
	echo "--------------------------------"
	echo "------ [ WAITING FOR DB ] ------"
	echo "--------------------------------"
	sleep 5
done

XDEBUG_MODE=off php bin/console doctrine:migration:migrate

chown -R 1000:1000 /var/www/html/

nohup supervisord

echo "----------------------------------"
echo "----------------------------------"
echo "------ [ STARTUP FINISHED ] ------"
echo "----------------------------------"
echo "----------------------------------"
php-fpm