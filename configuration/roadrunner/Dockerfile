ARG PHP_VERSION=7.3.10
ARG ROADRUNNER_VERSION=1.4.8
ARG COMPOSER_VERSION=1.9

ARG FROM_PHP_VERSION="php:${PHP_VERSION}"
ARG FROM_ROADRUNNER_VERSION="512k/roadrunner:${ROADRUNNER_VERSION}"
ARG FROM_COMPOSER_VERSION="composer:${COMPOSER_VERSION}"

FROM $FROM_COMPOSER_VERSION AS composer
FROM $FROM_ROADRUNNER_VERSION AS roadrunner
FROM $FROM_PHP_VERSION

ENV \
    COMPOSER_ALLOW_SUPERUSER="1" \
    COMPOSER_HOME="/tmp/composer" \
    OWN_SSL_CERT_DIR="/ssl-cert" \
    OWN_SSL_CERT_LIFETIME=1095


# Set Environment Variables
ENV DEBIAN_FRONTEND noninteractive

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=roadrunner /usr/bin/rr /usr/bin/rr


# Update sources list, install latest version of libraries and then install and configure the "zip" package & extension
RUN set -xe; \
    apt update -yqq && \
    pecl channel-update pecl.php.net && \
    apt install -yqq \
        apt-utils   \
        libzip-dev zip unzip dos2unix git mediainfo mkvtoolnix procps && \
        docker-php-ext-configure zip --with-libzip && \
        docker-php-ext-install zip && \
        php -m | grep -q 'zip'

###########################################################################
# SOAP:
###########################################################################

ARG INSTALL_SOAP=false

RUN if [ ${INSTALL_SOAP} = true ]; then \
    # Install the soap extension
    rm /etc/apt/preferences.d/no-debian-php && \
    apt-get -y install libxml2-dev php-soap && \
    docker-php-ext-install soap \
;fi

###########################################################################
# XSL:
###########################################################################

ARG INSTALL_XSL=false

RUN if [ ${INSTALL_XSL} = true ]; then \
    # Install the xsl extension
    apt-get -y install libxslt-dev && \
    docker-php-ext-install xsl \
;fi

###########################################################################
# xDebug:
###########################################################################

ARG INSTALL_XDEBUG=false

RUN if [ ${INSTALL_XDEBUG} = true ]; then \
  # Install the xdebug extension
  if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
    pecl install xdebug-2.5.5; \
  else \
    pecl install xdebug; \
  fi && \
  docker-php-ext-enable xdebug \
;fi

# Copy xdebug configuration for remote debugging
COPY ./config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN sed -i "s/xdebug.remote_autostart=0/xdebug.remote_autostart=1/" /usr/local/etc/php/conf.d/xdebug.ini && \
    sed -i "s/xdebug.remote_enable=0/xdebug.remote_enable=1/" /usr/local/etc/php/conf.d/xdebug.ini && \
    sed -i "s/xdebug.cli_color=0/xdebug.cli_color=1/" /usr/local/etc/php/conf.d/xdebug.ini

###########################################################################
# Blackfire:
###########################################################################

ARG INSTALL_BLACKFIRE=false

RUN if [ ${INSTALL_XDEBUG} = false -a ${INSTALL_BLACKFIRE} = true ]; then \
    version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
;fi

###########################################################################
# PHP REDIS EXTENSION
###########################################################################

ARG INSTALL_PHPREDIS=false
ARG PHP_REDIS_VERSION=5.0.2

RUN git clone --branch ${PHP_REDIS_VERSION} https://github.com/phpredis/phpredis /tmp/phpredis \
    && cd /tmp/phpredis \
    && phpize  \
    && ./configure  \
    && make  \
    && make install \
    && make test \
    && echo 'extension=redis.so' > /usr/local/etc/php/conf.d/redis.ini \
    && rm -rf /tmp/phpredis

###########################################################################
# AMQP:
###########################################################################

ARG INSTALL_AMQP=false

RUN if [ ${INSTALL_AMQP} = true ]; then \
    # download and install manually, to make sure it's compatible with ampq installed by pecl later
    # install cmake first
    apt-get update && apt-get -y install cmake && \
    curl -L -o /tmp/rabbitmq-c.tar.gz https://github.com/alanxz/rabbitmq-c/archive/master.tar.gz && \
    mkdir -p rabbitmq-c && \
    tar -C rabbitmq-c -zxvf /tmp/rabbitmq-c.tar.gz --strip 1 && \
    cd rabbitmq-c/ && \
    mkdir _build && cd _build/ && \
    cmake .. && \
    cmake --build . --target install && \
    # Install the amqp extension
    pecl install amqp && \
    docker-php-ext-enable amqp && \
    # Install the sockets extension
    docker-php-ext-install sockets \
;fi

###########################################################################
# pcntl
###########################################################################

ARG INSTALL_PCNTL=false
RUN if [ ${INSTALL_PCNTL} = true ]; then \
    # Installs pcntl, helpful for running Horizon
    docker-php-ext-install pcntl \
;fi

###########################################################################
# bcmath:
###########################################################################

ARG INSTALL_BCMATH=false

RUN if [ ${INSTALL_BCMATH} = true ]; then \
    # Install the bcmath extension
    docker-php-ext-install bcmath \
;fi

###########################################################################
# GMP (GNU Multiple Precision):
###########################################################################

ARG INSTALL_GMP=false

RUN if [ ${INSTALL_GMP} = true ]; then \
    # Install the GMP extension
	  apt-get install -y libgmp-dev && \
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
      ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    ;fi && \
    docker-php-ext-install gmp \
;fi

###########################################################################
# PHP Memcached:
###########################################################################

ARG INSTALL_MEMCACHED=false

RUN if [ ${INSTALL_MEMCACHED} = true ]; then \
    # Install the php memcached extension
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
      curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/2.2.0.tar.gz"; \
    else \
      curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/master.tar.gz"; \
    fi \
    && mkdir -p memcached \
    && tar -C memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
    && ( \
        cd memcached \
        && phpize \
        && ./configure \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r memcached \
    && rm /tmp/memcached.tar.gz \
    && docker-php-ext-enable memcached \
;fi

###########################################################################
# Exif:
###########################################################################

ARG INSTALL_EXIF=false

RUN if [ ${INSTALL_EXIF} = true ]; then \
    # Enable Exif PHP extentions requirements
    docker-php-ext-install exif \
;fi

###########################################################################
# Opcache:
###########################################################################

ARG INSTALL_OPCACHE=false

RUN if [ ${INSTALL_OPCACHE} = true ]; then \
    docker-php-ext-install opcache \
;fi

# Copy opcache configration
COPY ./config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

###########################################################################
# Mysqli Modifications:
###########################################################################

ARG INSTALL_MYSQLI=false

RUN if [ ${INSTALL_MYSQLI} = true ]; then \
    docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql \
;fi


###########################################################################
# Human Language and Character Encoding Support:
###########################################################################

ARG INSTALL_INTL=false

RUN if [ ${INSTALL_INTL} = true ]; then \
    # Install intl and requirements
    apt-get install -y zlib1g-dev libicu-dev g++ && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl \
;fi

###########################################################################
# Node.JS and Yarn support:
###########################################################################

ARG INSTALL_NODE=false

RUN if [ ${INSTALL_NODE} = true ]; then \
    curl -sL https://deb.nodesource.com/setup_11.x | bash - && \
    apt update && \
    apt install -y nodejs && \
    npm install -g yarn && \
    mkdir /.cache && mkdir /.npm && mkdir /.config && \
    chown -R 1000:1000 /.cache && \
    chown -R 1000:1000 /.npm && \
    chown -R 1000:1000 /.config \
;fi

###########################################################################
# GHOSTSCRIPT:
###########################################################################

ARG INSTALL_GHOSTSCRIPT=false

RUN if [ ${INSTALL_GHOSTSCRIPT} = true ]; then \
    # Install the ghostscript extension
    # for PDF editing
    apt-get install -y \
    poppler-utils \
    ghostscript \
;fi

###########################################################################
# LDAP:
###########################################################################

ARG INSTALL_LDAP=false

RUN if [ ${INSTALL_LDAP} = true ]; then \
    apt-get install -y libldap2-dev && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-install ldap \
;fi


###########################################################################
# Image optimizers:
###########################################################################

USER root

ARG INSTALL_IMAGE_OPTIMIZERS=false

RUN if [ ${INSTALL_IMAGE_OPTIMIZERS} = true ]; then \
    apt-get install -y jpegoptim optipng pngquant gifsicle \
;fi

###########################################################################
# ImageMagick:
###########################################################################

USER root

ARG INSTALL_IMAGEMAGICK=false

RUN if [ ${INSTALL_IMAGEMAGICK} = true ]; then \
    apt-get install -y libmagickwand-dev imagemagick && \
    pecl install imagick && \
    docker-php-ext-enable imagick \
;fi

###########################################################################
# IMAP:
###########################################################################

ARG INSTALL_IMAP=false

RUN if [ ${INSTALL_IMAP} = true ]; then \
    apt-get install -y libc-client-dev libkrb5-dev && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap \
;fi

###########################################################################
# APCU:
###########################################################################

ARG INSTALL_APCU=false

RUN if [ ${INSTALL_APCU} = true ]; then \
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
        pecl install -a apcu-4.0.11; \
    else \
        pecl install apcu; \
    fi && \
    docker-php-ext-enable apcu \
;fi

###########################################################################
# YAML:
###########################################################################

USER root

ARG INSTALL_YAML=false

RUN if [ ${INSTALL_YAML} = true ]; then \
    apt-get install libyaml-dev -y ; \
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
        pecl install -a yaml-1.3.2; \
    else \
        pecl install yaml; \
    fi && \
    docker-php-ext-enable yaml \
;fi

###########################################################################
# MySQL Client:
###########################################################################

USER root

ARG INSTALL_MYSQL_CLIENT=false

RUN if [ ${INSTALL_MYSQL_CLIENT} = true ]; then \
    apt-get update -yqq && \
    apt-get -y install mysql-client \
;fi

###########################################################################
# ping:
###########################################################################

USER root

ARG INSTALL_PING=false

RUN if [ ${INSTALL_PING} = true ]; then \
    apt-get update -yqq && \
    apt-get -y install inetutils-ping \
;fi

###########################################################################
# FFMPEG:
###########################################################################

USER root

ARG INSTALL_FFMPEG=false

RUN if [ ${INSTALL_FFMPEG} = true ]; then \
    apt-get update -yqq && \
    apt-get -y install ffmpeg \
;fi

RUN rm -rf /app /home/user ${COMPOSER_HOME} /var/cache/apk/* \
        && mkdir /app /home/user ${COMPOSER_HOME} \
        && composer global require 'hirak/prestissimo' --no-interaction --no-suggest --prefer-dist \
        && ln -s /usr/bin/composer /usr/bin/c


COPY ./etc/php/php.ini /usr/local/etc/php/php.ini
COPY ./etc/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./app-entrypoint.sh /app-entrypoint.sh
COPY ./scheduler.sh /scripts/scheduler.sh

RUN dos2unix /app-entrypoint.sh
RUN dos2unix /scripts/scheduler.sh

#RUN mkdir -p ${OWN_SSL_CERT_DIR} \
#            && cd ${OWN_SSL_CERT_DIR} \
#            && openssl genrsa -passout pass:x -out self-signed.key 2048 \
#            && cp self-signed.key self-signed.key.orig \
#            && openssl rsa -passin pass:x -in self-signed.key.orig -out self-signed.key \
#            && openssl req -new -key self-signed.key -out cert.csr \
#                -subj "/C=RU/ST=RU/L=Somewhere/O=SomeOrg/OU=IT Department/CN=example.com" \
#            && openssl x509 -req -days ${OWN_SSL_CERT_LIFETIME} -in cert.csr -signkey self-signed.key -out self-signed.crt \
#            && ls -lh

WORKDIR /app



# DO NOT OVERRIDE ENTRYPOINT IF YOU CAN AVOID IT! @see <https://github.com/docker/docker.github.io/issues/6142>
ENTRYPOINT ["/app-entrypoint.sh"]
CMD ["rr", "serve", "-c", "/app/.rr.yml"]
