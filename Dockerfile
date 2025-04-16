# Use official PHP 8.4 Apache image as base
FROM php:8.4-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    && a2enmod rewrite

# Configure PHP settings
COPY src/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy application files
COPY src/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Make sure any executable scripts are runnable
RUN if [ -f "/var/www/html/config/sql.sh" ]; then chmod +x /var/www/html/config/sql.sh; fi

# Set environment variables
ENV DB_HOST=db \
    DB_NAME=estoque \
    DB_USER=admin \
    DB_PASSWORD=password \
    DB_PORT=5432

# Expose port 80
EXPOSE 80

# Use the default Apache start command
CMD ["apache2-foreground"]