FROM nginx:stable-alpine

RUN apk upgrade --update

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/error/ /etc/nginx/error/

RUN mkdir -p /var/www/html/phpapp && \
    chmod -R 777 /var/www/html
COPY Symfony /var/www/html/phpapp/Symfony

# Run
COPY docker/nginx/run.sh /var/run/run.sh
CMD  ["/var/run/run.sh", "&"]
