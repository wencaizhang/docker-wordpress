FROM wordpress:latest
RUN wp-config.php /var/www/html/wp-config.php && chmod -R 777 /var/www/html/wp-content