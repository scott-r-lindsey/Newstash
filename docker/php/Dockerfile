FROM php:7.1-fpm-alpine 
                                        
ENV BUILD_DEPS alpine-sdk autoconf coreutils gettext-dev libxml2-dev openssl-dev postgresql-dev pcre-dev
ENV PERSISTENT_DEPS sqlite zip openssl subversion coreutils
ENV INSTALL_EXTENSIONS dom fileinfo gettext json opcache simplexml zip pdo pdo_mysql

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
    apk del .build-deps && \            
    rm -rf /tmp/*

# Copy shell commands                   
COPY run.sh wait.sh /var/run/

# Fix web user permissions              
RUN sed -i "s/82:82/1000:1000/g" /etc/passwd && \
    sed -i "s/:82:/:1000:/g" /etc/group

# Copy project code
COPY Symfony /var/www/html/phpapp/Symfony
RUN chown -R www-data:www-data /var/www/html/phpapp/Symfony /home/www-data
        
# ----------------------------------    
USER www-data
CMD ["/var/run/run.sh"]                 
WORKDIR /var/www/html/phpapp/Symfony
