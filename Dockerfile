FROM php:8.2-apache

WORKDIR /var/www/html

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring bcmath gd zip

# Enable Apache rewrite
RUN a2enmod rewrite

# Configure Apache template for Laravel + Render
COPY apache-config-template /etc/apache2/sites-available/000-default.conf

# Create startup script for dynamic port binding
RUN echo '#!/bin/bash\n\
PORT=${PORT:-8080}\n\
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf\n\
apache2-foreground' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ✅ COPY FULL PROJECT FIRST (THIS FIXES THE ERROR)
COPY . .

# ✅ THEN install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Expose port (Render will override)
EXPOSE 8080

# Use custom startup script
ENTRYPOINT ["/usr/local/bin/start.sh"]

