#!/bin/sh
service php5-fpm start
service nginx start
cd /var/www/participa
/usr/bin/php artisan key:generate
./replace_env.sh .env .env_expanded
mv .env_expanded .env
/usr/bin/php artisan migrate