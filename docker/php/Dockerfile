FROM php:8.2-fpm

# Install common extensions (pdo, pdo_mysql)
RUN apt-get update && apt-get install -y bash
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www

COPY . /var/www

RUN chown -R www-data:www-data /var/www || true

EXPOSE 9000
CMD ["php-fpm"]
