# Modern Store Ecommerce Backend

A Laravel-based ecommerce backend API for product browsing, customer authentication, order management, payment processing, and admin operations.

## Overview

This project is a REST API built with Laravel 13 and PHP 8.3. It provides a strong backend foundation for an ecommerce storefront, including:

- User registration, login, email verification, password reset, and session management
- Product listing, featured products, and category discovery
- Order creation, order history, and cancellation flows
- Paystack payment initialization and webhook verification
- Admin analytics and management for products, users, orders, and settings
- Contact message management and customer communication workflows
- Inventory and event-driven order processing patterns

## Tech Stack

- PHP 8.3
- Laravel 13
- SQLite by default (configurable for MySQL/PostgreSQL)
- Laravel Sanctum for API authentication
- Laravel Queue for background jobs
- Laravel Vite for frontend asset bundling
- Paystack integration
- Resend mail integration
- Redis / database cache support

## Project Structure

```bash
app/
  Console/
  Domain/
    Auth/
    Inventory/
    Order/
    Product/
  Enums/
  Http/
    Controllers/
      Api/
      Admin/
  Models/
  Jobs/
  Listeners/
  Mail/
  Providers/
config/
database/
public/
routes/
resources/
tests/
```

## Features

### Customer API

- Register and login users
- Verify and resend email verification
- Reset forgotten passwords
- Fetch current authenticated user
- Update profile and avatar
- Browse product catalog and featured items
- View categories
- Create and view orders
- Cancel orders
- View user activity and analytics

### Admin API

- Dashboard analytics and revenue summaries
- Product management: list, create, update, delete, feature toggle
- Order management and status updates
- User management and profile updates
- Coupon management
- Store settings management
- System actions: cache clear, backups, optimization
- Contact message admin access and replies

### Payment and Automation

- Paystack payment initialization and verification
- Payment webhook handling
- Queue-based job processing for notifications and order updates
- Event-driven business logic for inventory and analytics updates

## Prerequisites

Before starting, make sure you have:

- PHP 8.3+
- Composer
- Node.js and npm
- A database engine available (SQLite works out of the box)

## Installation

1. Clone the repository:

```bash
git clone <https://github.com/devTemilorun/devtemilorun-ecommerce-backend>
cd devtemilorun-ecommerce-backend
```

2. Install PHP dependencies:

```bash
composer install
```

3. Copy the environment file:

```bash
cp .env.example .env
```

4. Generate the app key:

```bash
php artisan key:generate
```

5. Set up the database:

For SQLite (default config):

```bash
touch database/database.sqlite
php artisan migrate
```

6. Install frontend dependencies and build assets:

```bash
npm install
npm run build
```

7. Start the app:

```bash
php artisan serve
```

To run the full local stack with queue and Vite dev server:

```bash
composer run dev
```

## Environment Configuration

The project includes a sample environment file in `.env.example`. Update the values according to your environment.

Key variables include:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PAYSTACK_CALLBACK_URL=
```

If you are using a different database, update the `DB_*` settings in `.env` accordingly.

## API Routes

The project exposes API routes under `/api`.

### Public routes

```http
POST /api/auth/register
POST /api/auth/login
POST /api/auth/forgot-password
POST /api/auth/reset-password
POST /api/auth/verify-email
POST /api/auth/resend-verification
GET /api/auth/check

GET /api/products
GET /api/products/featured
GET /api/products/{id}
GET /api/categories

POST /api/paystack/initialize
GET /api/paystack/callback
POST /api/paystack/webhook
POST /api/paystack/verify

POST /api/contact/send
```

### Authenticated customer routes

```http
GET /api/user
POST /api/auth/logout
GET /api/orders
GET /api/orders/{id}
POST /api/orders
POST /api/orders/{id}/cancel
GET /api/analytics/user
GET /api/user/activity
PUT /api/user
POST /api/user/avatar
```

### Admin routes

```http
GET /api/admin/analytics/dashboard
GET /api/admin/analytics/revenue
GET /api/admin/analytics/products
GET /api/admin/analytics/customers

GET /api/admin/products
POST /api/admin/products
GET /api/admin/products/{id}
PUT /api/admin/products/{id}
DELETE /api/admin/products/{id}
PATCH /api/admin/products/{id}/featured

GET /api/admin/orders
GET /api/admin/orders/{id}
PUT /api/admin/orders/{id}/status

GET /api/admin/users
GET /api/admin/users/{id}
PUT /api/admin/users/{id}
DELETE /api/admin/users/{id}

GET /api/admin/coupons
POST /api/admin/coupons
GET /api/admin/coupons/{id}
PUT /api/admin/coupons/{id}
DELETE /api/admin/coupons/{id}
POST /api/admin/coupons/{id}/toggle

GET /api/admin/settings
POST /api/admin/settings

POST /api/admin/system/clear-cache
POST /api/admin/system/backup
GET /api/admin/system/backups
POST /api/admin/system/optimize
```

## Database and Migration

Migrations are stored in `database/migrations` and can be run with:

```bash
php artisan migrate
```

To reset the database:

```bash
php artisan migrate:fresh
```

## Testing

Run the test suite with:

```bash
php artisan test
```

## Production Notes

For production deployments:

- set `APP_ENV=production`
- set `APP_DEBUG=false`
- configure a production database and cache driver
- add valid `PAYSTACK_*` keys
- configure mail credentials and queue workers
- run `php artisan optimize` and `php artisan config:cache`

## License

This project is licensed under the MIT License.

## Notes

This repository is a backend-only ecommerce API. If you are building a frontend, connect to the API endpoints defined in the `routes/api.php` file and use the generated authentication flow for customer and admin access.
