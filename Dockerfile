FROM php:8.2-fpm

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

# Add a non-root user and group
RUN groupadd -g 1000 roth && useradd -u 1000 -g roth -m roth

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create storage directory
RUN mkdir -p storage/logs && chown -R roth:roth storage

# Set permissions for the log file
RUN touch storage/logs/laravel.log && chown roth:roth storage/logs/laravel.log

# Set ownership to the roth user and group
COPY . /var/www
RUN chown -R roth:roth /var/www

# Set SELinux context if necessary
# RUN chcon -Rt svirt_sandbox_file_t storage

# Switch to the roth user
USER roth
