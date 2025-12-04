<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## ORSM Web Development Project

This is an Order & Stock/Inventory Management system built with Laravel, featuring reports, sales dashboards, and payment simulation tools.

### Features

#### Sales & Reporting (Member 4)

-   **Admin Sales Dashboard**: Real-time charts (daily, weekly, monthly sales), category/supplier breakdowns, low stock alerts
-   **CSV Export**: Stream large order datasets without memory pressure
-   **PDF Export**: Single or chunked PDF generation with DomPDF, supports async processing for large exports
-   **Async PDF Export**: Queue-based batch processing with polling UI and ZIP delivery
-   **Payment Simulation**: Admin tools to simulate COD, successful, and failed payments for testing

#### Key Components

-   `app/Http/Controllers/ReportsController.php` — Reports and export endpoints
-   `app/Http/Controllers/PaymentController.php` — Payment simulation API
-   `app/Jobs/GenerateSalesPdfReport.php` — Async PDF chunking and ZIP creation
-   `app/Utilities/ZipHelper.php` — Pure-PHP ZIP creator (no ZipArchive extension required)
-   `resources/views/admin/sales_dashboard.blade.php` — Admin dashboard with Chart.js
-   `resources/views/admin/payments/simulator.blade.php` — Payment simulator UI

### Database Setup

**Required columns on the `orders` table** (see migration `2025_12_02_175005_create_orders_table.php`):

-   `order_date` (datetime) — date of the order
-   `total_amount` (decimal) — total amount for the order
-   `payment_status` (string) — 'pending', 'completed', 'failed'
-   `payment_method` (string) — 'Cash on Delivery', 'Credit Card', etc.

**Setup commands:**

```bash
php artisan migrate
php artisan db:seed
```

### Reporting Features

#### Admin Sales Dashboard

Access at `/admin/sales` (requires authentication + admin role):

-   Filter by date range, category, supplier
-   View daily/weekly/monthly sales summaries
-   Export to CSV or PDF

#### CSV Export

```
GET /admin/sales/export/csv?start=2025-11-01&end=2025-11-30
```

Streams CSV data without loading entire dataset into memory.

#### PDF Export (Sync or Async)

**Synchronous** (small exports, ≤ 200 orders):

```
GET /admin/sales/export/pdf?start=2025-11-01&end=2025-11-30
```

Returns a single PDF download.

**Asynchronous** (large exports):

```
GET /admin/sales/export/pdf?start=2025-11-01&end=2025-11-30&async=1
```

Returns JSON with batch ID; client polls `/admin/sales/export/check/{batch}` until status is 'ready', then downloads at `/admin/sales/export/download/{batch}`.

#### Smoke Test

```bash
php artisan report:smoke-test [start_date] [end_date]
# Example:
php artisan report:smoke-test 2025-11-01 2025-11-30
```

Generates CSV and PDF reports into `storage/app/reports` for testing.

#### Async Dispatch & Worker

```bash
# Dispatch an async export job
php artisan report:dispatch-async 2025-11-01 2025-11-30

# Run the queue worker (once or continuously)
php artisan queue:work --once
php artisan queue:work # runs indefinitely
```

### Payment Simulation

Access at `/admin/payments` (requires authentication + admin role):

**Endpoints:**

-   `POST /admin/payments/{orderId}/simulate-cod` — Set payment to COD (pending)
-   `POST /admin/payments/{orderId}/simulate-success` — Mark payment completed
-   `POST /admin/payments/{orderId}/simulate-failed` — Mark payment failed
-   `POST /admin/payments/bulk-update` — Update multiple orders in bulk
-   `GET /admin/payments/stats` — Get payment statistics (JSON)
-   `POST /admin/payments/create-test` — Create a test order with specified payment method

**Example request (bulk update):**

```json
POST /admin/payments/bulk-update
{
  "order_ids": [1, 2, 3],
  "payment_status": "completed",
  "payment_method": "Credit Card"
}
```

### Deployment Notes

#### Environment Requirements

-   **PHP Extensions**:
    -   `barryvdh/laravel-dompdf` for PDF generation (included)
    -   `zip` extension (optional) — if not available, `ZipHelper` provides pure-PHP fallback
-   **Laravel Queue**: Configured to use database driver by default. For production, consider switching to Redis/SQS.
-   **Storage**: Reports are written to `storage/app/reports/`; ensure the directory is writable and has sufficient disk space for temporary PDFs and ZIPs.

#### Migration & Deployment Steps

1. Pull the latest changes
2. Run `composer install` (if new dependencies added)
3. Run `php artisan migrate` to apply database changes
4. Seed test data (optional): `php artisan db:seed`
5. Run smoke test to verify setup: `php artisan report:smoke-test`
6. Start queue worker (production): Set up a supervisor/systemd service to run `php artisan queue:work`

#### Schema Detection

The reporting code uses schema detection (`Schema::hasColumn()`) to handle environments where `order_date` or `total_amount` columns may differ. The code will:

-   Use `created_at` if `order_date` is unavailable
-   Sum `order_details.subtotal` if `total_amount` is unavailable

This ensures compatibility across varying database schemas.

#### Logging

Diagnostic logs for async PDF exports are written to `storage/logs/laravel.log`:

-   `GenerateSalesPdfReport::handle start` — Job initialization (total orders, chunk size)
-   `GenerateSalesPdfReport::wrote PDF part` — Each chunk (file path, size)
-   `GenerateSalesPdfReport::ZIP created successfully` — ZIP info (size, file count)
-   `GenerateSalesPdfReport::completed` — Job completion

Review logs if exports fail or produce empty ZIPs.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
