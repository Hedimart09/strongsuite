#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 5

# Clear any cached config that might have wrong values
php artisan config:clear || true
php artisan cache:clear || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed roles and permissions
echo "Seeding roles and permissions..."
php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder --force || true

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Application ready!"

# Execute the main container command
exec "$@"
