FROM dunglas/frankenphp:php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Expose port 80
EXPOSE 80

# Start FrankenPHP
CMD ["frankenphp", "run"]