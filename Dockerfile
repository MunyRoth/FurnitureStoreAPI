#FROM php:8.2-alpine3.18
#
## install composer
## Update package lists and install required packages
#RUN apk update && apk add --no-cache curl git  mysql-client mysql-dev
#
## Install PHP extensions
#RUN  docker-php-ext-install pdo_mysql
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#
## copy all of the file in folder to /src
#WORKDIR /src
#COPY .env.example /src/.env
#COPY . .
#
## install all of the dependencies  in composer.json
#RUN composer update
#RUN php artisan key:generate
#
##serve laravel
#CMD php artisan serve --host=0.0.0.0 --port=8080

FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user
