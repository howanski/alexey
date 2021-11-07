#!/bin/bash
cd /var/www/html
rm -rf var/cache
rm -rf public/build
XDEBUG_MODE=off composer install
XDEBUG_MODE=off yarn install
XDEBUG_MODE=off yarn encore prod

until XDEBUG_MODE=off php bin/console doctrine:query:sql -q "show tables"; do
	echo "--------------------------------"
	echo "------ [ WAITING FOR DB ] ------"
	echo "--------------------------------"
	sleep 5
done

XDEBUG_MODE=off php bin/console doctrine:migration:migrate

setfacl -dR -m u:www-data:rwX /var/www/html
setfacl -R -m u:www-data:rwX /var/www/html

nohup supervisord

echo "----------------------------------"
echo "----------------------------------"
echo "------ [ STARTUP FINISHED ] ------"
echo "----------------------------------"
echo "----------------------------------"
