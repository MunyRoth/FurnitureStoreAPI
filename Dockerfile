FROM php:8.2-alpine3.18

# install composer
# Update package lists and install required packages
RUN apk update && apk add --no-cache curl git  mysql-client mysql-dev

# Install PHP extensions
RUN  docker-php-ext-install pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# copy all of the file in folder to /src
WORKDIR /src
COPY .env.example /src/.env
COPY . .

# install all of the dependencies  in composer.json
RUN composer update

#serve laravel
CMD php artisan serve --host=0.0.0.0 --port=8080
