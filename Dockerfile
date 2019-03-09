FROM wordpress:latest
COPY wp-config.php /var/www/html/wp-config.php
RUN chmod -R 777 /var/www/
