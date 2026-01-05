# Use official PHP CLI image
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    mbstring \
    xml \
    curl \
    zip \
    gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
RUN npm ci && npm run build

# Create storage directories
RUN mkdir -p storage/app/public/members/photos \
    && mkdir -p storage/framework/{cache,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Set permissions (run as root, so use 777 for storage)
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port (Railway will set PORT env var)
EXPOSE 8080

# Start script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
