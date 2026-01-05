#!/bin/bash
set -e

echo "Starting StrongSuite deployment..."

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Clear any cached config that might have wrong values
echo "Clearing cached configuration..."
php artisan config:clear || true
php artisan cache:clear || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force

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
