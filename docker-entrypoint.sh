#!/bin/bash
set -e

echo "Starting StrongSuite deployment..."

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Ensure storage directory has correct permissions (important for Railway volumes)
echo "Setting storage permissions..."
chmod -R 777 /var/www/html/storage/app/public || true
chmod -R 777 /var/www/html/storage/framework || true
chmod -R 777 /var/www/html/storage/logs || true

# Ensure member photos directory exists
mkdir -p /var/www/html/storage/app/public/members/photos || true

# Clear any cached config that might have wrong values
echo "Clearing cached configuration..."
php artisan config:clear || true
php artisan cache:clear || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Create storage link
echo "Creating storage symlink..."
php artisan storage:link --force || true

# Seed roles and permissions
echo "Seeding roles and permissions..."
php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder --force || true

# Seed admin users
echo "Seeding admin users..."
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder --force || true

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Application ready! Starting server on port ${PORT:-8080}..."

# Execute the main container command
exec "$@"
