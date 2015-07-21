FROM debian:jessie

RUN apt-get update && apt-get -y install nginx php5-fpm php5-cli php5-mcrypt php5-gd php5-mysql php5-curl php5-json php5-redis git curl
RUN apt-get -y autoremove && apt-get clean

RUN git clone https://github.com/mxabierto/participa.git /var/www/participa

RUN php5enmod mcrypt

ADD participa.nginx /etc/nginx/sites-available/default
ADD start.sh /var/www/participa/start.sh

# Set PHP-FMP
RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php5/fpm/php-fpm.conf
RUN sed -i -e "s/;listen.mode = 0660/listen.mode = 0750/g" /etc/php5/fpm/pool.d/www.conf

WORKDIR /var/www/participa
RUN /usr/bin/curl -sS https://getcomposer.org/installer |/usr/bin/php
RUN /bin/mv /var/www/participa/composer.phar /usr/local/bin/composer
RUN /usr/local/bin/composer install --no-dev --prefer-source
RUN /bin/chown www-data:www-data -R /var/www/participa
RUN /bin/chmod -R 755 /var/www/participa/storage
ADD .env.docker /var/www/participa/.env

RUN chmod +x start.sh

EXPOSE 80 443

CMD [ "bash", "-c", "/var/www/participa/start.sh;bash" ]