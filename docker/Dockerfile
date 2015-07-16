FROM debian:jessie

RUN apt-get update && apt-get -y install nginx php5-fpm php5-cli php5-mcrypt php5-gd php5-mysql git curl
RUN apt-get -y autoremove && apt-get clean

RUN git clone https://github.com/mxabierto/participa.git /var/www/participa

RUN php5enmod mcrypt

ADD participa.nginx /etc/nginx/sites-available/default

# RUN touch -p /var/run/php5-fpm.sock
RUN sed -i '/^listen = /clisten = 127.0.0.1:9000' /etc/php5/fpm/pool.d/www.conf
RUN sed -i '/^listen.mode = /clisten.mode = 0660' /etc/php5/fpm/pool.d/www.conf

WORKDIR /var/www/participa
RUN /usr/bin/curl -sS https://getcomposer.org/installer |/usr/bin/php
RUN /bin/mv /var/www/participa/composer.phar /usr/local/bin/composer
# RUN composer config -g github-oauth.github.com $GITHUB_TOKEN
RUN /usr/local/bin/composer install --no-dev
RUN /bin/chown www-data:www-data -R /var/www/participa
RUN /bin/chmod -R 755 /var/www/participa/storage
ADD .env.docker /var/www/participa/.env

EXPOSE 80 443

CMD ["php5-fpm"]
CMD ["nginx", "-g", "daemon off;"]
