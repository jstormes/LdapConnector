FROM php:7

############################################################################
# Install required libraries, should be the same across dev, QA, etc...
############################################################################
RUN apt-get update \
    && apt-get install -y curl wget git zip unzip zlib1g-dev libpng-dev \
       gnupg2 libldap2-dev ssl-cert joe \
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
ENV PATH /opt/project/vendor/bin:/opt/project/bin:~/bin:~/.composer/vendor/bin:$PATH

############################################################################
# Setup XDebug, always try and start XDebug connection to host.docker.internal
############################################################################
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
ENV PHP_IDE_CONFIG="serverName=ldap"

#############################################################################
# Setup OpenLDAP server
#############################################################################
ADD assets/ssl/certs/ /etc/ssl/certs/
ADD assets/ssl/private/ /etc/ssl/private/
ADD assets/ldap/debconfig-set-selections.txt /etc/ldap
RUN cat /etc/ldap/debconfig-set-selections.txt | debconf-set-selections \
    && rm /etc/ldap/debconfig-set-selections.txt \
    && apt-get install -y slapd ldap-utils ldapscripts syslog-ng-core gettext \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
# LDAP Config
ADD assets/ldap/ldif /etc/ldap/ldif
ADD assets/ldap/ldap.conf /etc/ldap/
RUN usermod -aG ssl-cert openldap \
    && /bin/bash -c "service slapd start" \
    && sleep 5 \
    && ldapmodify -H ldapi:// -Y EXTERNAL -f /etc/ldap/ldif/ssl.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/us_cn.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/test_group.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/test.u_user.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/bamboo-user.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/Arlington-Development.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/DL-ARL-Development.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/US-Development.ldif \
    && ldapadd -H ldapi:// -x -w naked  -D "cn=admin,dc=loopback,dc=world" -f /etc/ldap/ldif/US-VPN-Users.ldif

WORKDIR /opt/project

