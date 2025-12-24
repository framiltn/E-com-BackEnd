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

# Set Apache document root to /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ✅ COPY FULL PROJECT FIRST (THIS FIXES THE ERROR)
COPY . .

# ✅ THEN install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Render dynamic port
ENV PORT=8080
RUN sed -i "s/Listen 80/Listen \${PORT}/g" /etc/apache2/ports.conf

EXPOSE 8080

CMD ["apache2-foreground"]
