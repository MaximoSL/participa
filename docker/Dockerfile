FROM debian:jessie

RUN \
  # Install dependencies
  apt-get update && \
  apt-get -y --fix-missing install \
    nginx \
    php5-fpm \
    php5-cli \
    php5-mcrypt \
    php5-gd \
    php5-mysql \
    php5-curl \
    php5-json \
    php5-redis \
    git \
    curl && \
  # Cleanup
  apt-get -y autoremove && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* && \
  # Add code
  git clone https://github.com/mxabierto/participa.git /var/www/participa

# Add the required configuration files
ADD participa.nginx /etc/nginx/sites-available/default
ADD .env.docker /var/www/participa/.env
ADD start.sh /var/www/participa/start.sh
ADD replace_env.sh /var/www/participa/replace_env.sh

RUN \
  chmod +x /var/www/participa/start.sh && \
  chmod +x /var/www/participa/replace_env.sh && \
  # Config PHP
  php5enmod mcrypt && \
  sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php5/fpm/php-fpm.conf && \
  sed -i -e "s/;listen.mode = 0660/listen.mode = 0750/g" /etc/php5/fpm/pool.d/www.conf && \
  # App setup
  cd /var/www/participa && \
  curl -sS https://getcomposer.org/installer |/usr/bin/php && \
  mv /var/www/participa/composer.phar /usr/local/bin/composer && \
  /usr/local/bin/composer install --no-dev --prefer-source && \
  chown www-data:www-data -R /var/www/participa && \
  chmod -R 755 /var/www/participa/storage && \
  # Forward access and error logs to log collector
  ln -sf /dev/stdout /var/log/nginx/access.log && \
  ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80 443

CMD [ "bash", "-c", "/var/www/participa/start.sh;bash" ]