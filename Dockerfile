FROM php:7

############################################################################
# Install required libraries, should be the same across dev, QA, etc...
############################################################################
RUN apt-get update \
    && apt-get install -y curl wget git zip unzip zlib1g-dev libpng-dev \
       gnupg2 libldap2-dev ssl-cert \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install gd zip ldap

############################################################################
# Install composer
############################################################################
RUN cd ~ \
    && wget https://getcomposer.org/installer \
    && php installer \
    && rm installer \
    && mkdir bin \
    && mv composer.phar bin/composer \
    && chmod u+x bin/composer \
    && cp bin/composer /opt/
# Add our script files so they can be found
ENV PATH /opt/vendor/bin:~/bin:~/.composer/vendor/bin:$PATH

############################################################################
# Setup XDebug, always try and start XDebug connection to host.docker.internal
############################################################################
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini
ENV PHP_IDE_CONFIG="serverName=ldap_test"

WORKDIR /opt/project
#ENTRYPOINT /opt/vendor/bin/phpunit
