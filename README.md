# Strongsuite - Gym Management System

A comprehensive gym management system built with Laravel 12, Inertia.js, Vue 3, and PostgreSQL.

## Features

### Member Management
- Member registration and profile management
- QR code generation for contactless check-in
- Member photo uploads
- Advanced search and filtering
- Member status tracking (Active/Inactive)

### Membership Plans & Subscriptions
- Flexible membership plans (Daily, Weekly, Monthly, Quarterly, Annual)
- Subscription management with automatic status tracking
- Subscription renewal and cancellation
- Multiple currency support

### Attendance Tracking
- QR code scanning for quick check-in/check-out
- Manual check-in option
- Attendance history and duration tracking
- Real-time attendance monitoring

### Billing & Payments
- Automated invoice generation
- Multiple payment gateway support (Paystack, Flutterwave, Stripe, Manual/Cash)
- Payment tracking and status management
- PDF invoice generation

### Reporting & Analytics
- Attendance reports with peak hours analysis
- Revenue reports by payment method
- Member growth and activity reports
- Customizable date range filtering

### Staff & Permissions
- Role-based access control (Admin, Receptionist, Trainer)
- Granular permissions system
- Staff management with password control

### Gym Settings
- Customizable gym information
- Logo upload
- Timezone and currency configuration
- Tax rate management
- Payment gateway configuration

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Vue 3 (Composition API), Inertia.js v2
- **Styling**: Tailwind CSS v4
- **Database**: PostgreSQL
- **Testing**: Pest PHP
- **Authentication**: Laravel Fortify with 2FA support
- **Code Quality**: Laravel Pint

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18
- PostgreSQL >= 14
- NPM or Yarn

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd strongsuite
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Setup

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Configure your database connection in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=strongsuite
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Generate application key:

```bash
php artisan key:generate
```

### 5. Database Setup

Run migrations:

```bash
php artisan migrate
```

Seed the database with demo data:

```bash
php artisan db:seed
```

This will create:
- Admin user: `aaritsolution@gmail.com` / `password`
- 5 membership plans
- 50 active members with subscriptions and attendance records
- 10 inactive members
- Roles and permissions

### 6. Storage Link

Create the storage symlink for file uploads:

```bash
php artisan storage:link
```

### 7. Build Assets

Development build:

```bash
npm run dev
```

Production build:

```bash
npm run build
```

### 8. Run the Application

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Default Credentials

After seeding, you can log in with:

- **Email**: aaritsolution@gmail.com
- **Password**: password

**Important**: Change these credentials immediately after first login!

## Testing

Run all tests:

```bash
php artisan test
```

Run specific test file:

```bash
php artisan test tests/Feature/MemberManagementTest.php
```

Run with filter:

```bash
php artisan test --filter=testName
```

## Code Formatting

Format code with Laravel Pint:

```bash
vendor/bin/pint
```

## Development

### Running Development Server

Terminal 1 - Laravel development server:
```bash
php artisan serve
```

Terminal 2 - Vite dev server (hot module replacement):
```bash
npm run dev
```

### Database Management

Fresh migration with seeding:
```bash
php artisan migrate:fresh --seed
```

## Deployment

### Production Checklist

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure production database credentials
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Run `php artisan view:cache`
7. Run `npm run build`
8. Set up proper file permissions for `storage` and `bootstrap/cache`
9. Configure your web server (Nginx/Apache)
10. Set up SSL certificate
11. Configure payment gateways in `.env`

### Environment Variables

Key environment variables to configure:

```env
APP_NAME="Strongsuite"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=your_db_host
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Payment Gateways (Optional)
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=

FLUTTERWAVE_PUBLIC_KEY=
FLUTTERWAVE_SECRET_KEY=

STRIPE_KEY=
STRIPE_SECRET=
```

## Project Structure

```
strongsuite/
├── app/
│   ├── Http/Controllers/   # Application controllers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic services
│   └── ...
├── database/
│   ├── factories/          # Model factories for testing
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── js/
│   │   ├── components/    # Vue components
│   │   ├── layouts/       # Layout components
│   │   └── pages/         # Inertia pages
│   └── css/               # Stylesheets
├── routes/
│   ├── web.php            # Web routes
│   └── settings.php       # Settings routes
└── tests/
    ├── Feature/           # Feature tests
    └── Unit/              # Unit tests
```

## Key Features in Detail

### QR Code Check-in

Members are assigned unique QR codes that can be scanned at the gym entrance for quick check-in/check-out.

### Multi-Currency Support

The system supports multiple currencies (GHS, NGN, USD, EUR, etc.) with proper formatting and calculation.

### Role-Based Access Control

Three predefined roles:
- **Admin**: Full system access
- **Receptionist**: Member management, attendance, and billing
- **Trainer**: View-only access to member and attendance information

### Automated Invoice Generation

Invoices are automatically generated when subscriptions are created, with customizable tax rates and support for partial payments.

## Support

For issues and questions, please contact the development team or create an issue in the repository.

## License

Proprietary - All rights reserved
