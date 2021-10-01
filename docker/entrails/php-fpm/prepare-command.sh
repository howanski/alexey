#!/bin/bash
cd /var/www/html
mkdir -p public/uploads/images
rm -rf public/build
composer install
yarn install
yarn encore dev

until php bin/console doctrine:query:sql -q "show tables"; do
	echo "--------------------------------"
	echo "------ [ WAITING FOR DB ] ------"
	echo "--------------------------------"
	sleep 5
done

php bin/console doctrine:migration:migrate

setfacl -dR -m u:www-data:rwX /var/www/html
setfacl -R -m u:www-data:rwX /var/www/html

nohup supervisord

echo "----------------------------------"
echo "----------------------------------"
echo "------ [ STARTUP FINISHED ] ------"
echo "----------------------------------"
echo "----------------------------------"
