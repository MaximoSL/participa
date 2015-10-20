#!/bin/sh

# Start PHP
service php5-fpm start

# Setup
cd /var/www/participa
curl -sS https://getcomposer.org/installer |/usr/bin/php
mv /var/www/participa/composer.phar /usr/local/bin/composer
/usr/local/bin/composer install --no-dev --prefer-source
chown www-data:www-data -R /var/www/participa
chmod -R 755 /var/www/participa/storage
/usr/bin/php artisan key:generate
./replace_env.sh .env .env_expanded
mv .env_expanded .env
/usr/bin/php artisan migrate

# Start nginx in the foreground
exec nginx -g "daemon off;"
