# Multi-stage build for optimized production image
FROM composer:latest as composer

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Production stage
FROM php:8.2-apache

# Install system  dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy application code
COPY . .

# Create non-root user
RUN useradd --create-home --shell /bin/bash --user-group --uid 1001 laravel \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configure Apache for dynamic port
RUN a2enmod rewrite
RUN echo 'Listen ${PORT:-8000}' > /etc/apache2/ports.conf
COPY <<EOF /etc/apache2/sites-available/000-default.conf
<VirtualHost *:\${PORT:-8000}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Create optimized startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Wait for database if needed\n\
if [ "$DB_CONNECTION" = "pgsql" ] && [ -n "$DB_HOST" ]; then\n\
    echo "Waiting for PostgreSQL..."\n\
    while ! pg_isready -h $DB_HOST -p ${DB_PORT:-5432} -U $DB_USERNAME 2>/dev/null; do\n\
        sleep 1\n\
    done\n\
    echo "PostgreSQL is ready!"\n\
fi\n\
\n\
# Create storage link if needed\n\
if [ ! -L public/storage ] && [ -d storage/app/public ]; then\n\
    php artisan storage:link\n\
fi\n\
\n\
# Clear and cache config (only if APP_KEY is set)\n\
if [ -n "$APP_KEY" ]; then\n\
    php artisan config:cache\n\
    php artisan route:cache\n\
    php artisan view:cache\n\
fi\n\
\n\
# Health check endpoint\n\
echo "Application ready for requests"\n\
\n\
# Start Apache\n\
exec apache2-foreground' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Add health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:${PORT:-8000}/api/health || exit 1

# Switch to non-root user
USER laravel

# Expose dynamic port
EXPOSE ${PORT:-8000}

# Start the application
CMD ["/usr/local/bin/start.sh"]
