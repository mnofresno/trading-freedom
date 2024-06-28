FROM php:7.4-fpm

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
    cron \
    unzip \
    redis-tools

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Create application directory
RUN mkdir -p /var/www
WORKDIR /var/www

# Copy application source
COPY . /var/www

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN if ! id -u $user > /dev/null 2>&1; then \
    useradd -G www-data,root -u $uid -d /home/$user $user; \
    fi
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER root

# Configure PHP settings
RUN echo "memory_limit=1024M" >> /usr/local/etc/php/conf.d/php.ini
RUN echo "allow_url_fopen=on" >> /usr/local/etc/php/conf.d/php.ini

# Run Composer install
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction

# Run Artisan commands
RUN php artisan key:generate

# Set proper permissions
RUN chown -R $user:$user /var/www

USER $user
