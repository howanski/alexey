#!/bin/bash
cd /var/www/html
rm -rf var
rm -rf public/build
XDEBUG_MODE=off composer install
XDEBUG_MODE=off yarn install
XDEBUG_MODE=off yarn build

until XDEBUG_MODE=off php bin/console doctrine:query:sql -q "show tables"; do
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
