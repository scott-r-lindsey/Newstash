FROM php:7.3-fpm-alpine 

ENV BUILD_DEPS alpine-sdk autoconf coreutils gettext-dev libxml2-dev pcre-dev libzip-dev
ENV PERSISTENT_DEPS sqlite zip coreutils libzip libintl
ENV INSTALL_EXTENSIONS dom fileinfo gettext json opcache simplexml zip pdo pdo_mysql pcntl sysvshm sysvsem

# Install Packages
RUN apk upgrade --update && \
    apk add --no-cache --virtual .build-deps $BUILD_DEPS && \
    apk add --no-cache --virtual .persistent-deps $PERSISTENT_DEPS && \
    docker-php-ext-install $INSTALL_EXTENSIONS && \
    runDeps="$( \
        scanelf --needed --nobanner --recursive /usr/local \
            | awk '{ gsub(/,/, "\nso:", $2); print "so:" $2 }' \
            | sort -u \
            | xargs -r apk info --installed \
            | sort -u \
    )" && \
    apk add --no-cache --virtual .php-rundeps $runDeps && \
    pecl install mongodb && \
    pecl install redis && docker-php-ext-enable redis && \
    docker-php-ext-install bcmath && \
    echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/mongo.ini && \
    apk del .build-deps && \
    apk add libzip libintl && \
    rm -rf /tmp/*

# Copy shell commands
COPY docker/php/run.sh docker/php/wait.sh /var/run/

# Copy PHP tuning
COPY docker/php/perf.ini /usr/local/etc/php/conf.d/

# Fix web user permissions
RUN sed -i "s/82:82/1000:1000/g" /etc/passwd && \
    sed -i "s/:82:/:1000:/g" /etc/group

# Copy project code
COPY --chown=1000:1000 Symfony /var/www/html/phpapp/Symfony
RUN chown -R www-data:www-data /home/www-data

# ----------------------------------
USER www-data
CMD ["/var/run/run.sh"]
WORKDIR /var/www/html/phpapp/Symfony
