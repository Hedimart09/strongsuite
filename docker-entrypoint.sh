#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Seed roles and permissions
php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder --force || true

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Execute the main container command
exec "$@"
