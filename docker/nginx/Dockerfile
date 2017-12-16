FROM nginx:stable-alpine
                                        
RUN apk upgrade --update
         
COPY nginx.conf /etc/nginx/nginx.conf   
COPY default.conf /etc/nginx/conf.d/default.conf
COPY error/ /etc/nginx/error/           

RUN mkdir -p /var/www/html/phpapp && \
    chmod -R 777 /var/www/html          
COPY Symfony /var/www/html/phpapp/      
    
# Run
COPY run.sh /var/run/run.sh             
CMD  ["/var/run/run.sh", "&"]  
