# docker build -t mxabierto/participa .
# docker run -P -v /path/on/host:/var/www/participa mxabierto/participa
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
  rm -rf /var/lib/apt/lists/*

# Add the required configuration files
ADD participa.nginx /etc/nginx/sites-available/default

RUN \
  # Config PHP
  php5enmod mcrypt && \
  sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php5/fpm/php-fpm.conf && \
  sed -i -e "s/;listen.mode = 0660/listen.mode = 0750/g" /etc/php5/fpm/pool.d/www.conf && \
  # Forward access and error logs to log collector
  ln -sf /dev/stdout /var/log/nginx/access.log && \
  ln -sf /dev/stderr /var/log/nginx/error.log

# Code volume
VOLUME ["/var/www/participa"]

EXPOSE 80 443

ENTRYPOINT [ "/bin/bash", "-c", "/var/www/participa/start.sh" ]
