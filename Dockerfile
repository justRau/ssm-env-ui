FROM unit:1.31.1-php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Set proper permissions
RUN chown -R unit:unit /var/www/html \
    && chmod -R 755 /var/www/html

# Create NGINX Unit configuration
RUN echo '{\
    "listeners": {\
        "*:80": {\
            "pass": "applications/php"\
        }\
    },\
    "applications": {\
        "php": {\
            "type": "php",\
            "root": "/var/www/html",\
            "index": "index.php",\
            "script": "index.php"\
        }\
    }\
}' > /docker-entrypoint.d/config.json

# Expose port 80
EXPOSE 80