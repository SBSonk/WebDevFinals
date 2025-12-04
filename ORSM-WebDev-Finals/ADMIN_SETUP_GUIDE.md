# Admin Dashboard - Quick Setup Guide

## Getting Started

### Step 1: Run Migrations
```bash
php artisan migrate
```

This creates:
- `activity_logs` table
- `system_settings` table  
- Adds `role` and `is_active` columns to `users` table

### Step 2: Create First Admin User

You can create an admin user using the tinker shell:

```bash
php artisan tinker
```

Then in the tinker REPL:

```php
App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'test@example.com',
    'password' => bcrypt('password123'),
    'role' => 'admin',
    'is_active' => true,
]);
```

Or create a seeder in `database/seeders/CreateAdminSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CreateAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
```

Then run:
```bash
php artisan db:seed --class=CreateAdminSeeder
```

### Step 3: Access the Admin Panel

1. Log in with your admin user credentials
2. Navigate to `/admin/dashboard`
3. Start managing your system!

## Main Features Available

### Dashboard (`/admin/dashboard`)
- System overview with key statistics
- Recent orders and activities
- Quick action links to management areas

### User Management (`/admin/users`)
- View all users with pagination and search
- Add new users with role assignment
- Edit existing users
- Deactivate/activate users
- Delete users from the system

### System Settings (`/admin/settings`)
- Configure shop information (name, email, phone, address)
- Configure currency and tax rates
- All changes are logged for audit purposes

### Activity Logs (`/admin/logs`)
- View complete audit trail of all actions
- Filter by action type, user, date range
- Export logs to CSV
- View detailed information for each activity

## Integration Points

### Log Activities in Your Code

When you want to log user/admin actions, use:

```php
use App\Services\ActivityLogger;

// In your controller or service
ActivityLogger::logCreated('Product', $product->id, ['name' => $product->name]);
ActivityLogger::logUpdated('Order', $order->id, ['status' => 'completed']);
ActivityLogger::logDeleted('User', $user->id);
```

### Access System Settings

Get and set system configuration anywhere in your app:

```php
use App\Models\SystemSettings;

// Get settings
$shopName = SystemSettings::get('shop_name');

// Set settings programmatically
SystemSettings::set('maintenance_mode', true);
```

## Database Schema Quick Reference

### activity_logs table
```
- id (PK)
- user_id (FK) - Who did the action
- action - What was done (create, update, delete, etc.)
- subject_type - What was affected (User, Order, Product, etc.)
- subject_id - ID of the affected resource
- changes - JSON with before/after values
- ip_address - IP of the user
- user_agent - Browser info
- created_at, updated_at
```

### system_settings table
```
- id (PK)
- key (UNIQUE) - Setting identifier
- value - Setting value
- type - Data type (string, boolean, json, etc.)
- description - What this setting does
- created_at, updated_at
```

### users table (additions)
```
- role (ENUM) - admin | manager | customer
- is_active (BOOLEAN) - Account active/inactive
```

## Protecting Admin Routes

All admin routes require authentication. To add additional role-based protection, use:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function() {
    // Protected routes here
});
```

The `admin` middleware is available at `App\Http\Middleware\IsAdmin.php` but must be registered in `app/Http/Kernel.php`.

## Accessing Admin Features from Blade

In your Blade templates, you can check user roles:

```blade
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
@endif

@if(auth()->user()->isManager())
    <!-- Manager-specific content -->
@endif
```

## Default Admin Routes

```
/admin/dashboard              - Main dashboard
/admin/users                  - User list
/admin/users/create           - Add user form
/admin/users/{user}/edit      - Edit user form
/admin/settings               - Settings form
/admin/logs                   - Activity logs
/admin/logs/{log}             - Log details
```

## Next Steps

1. Integrate activity logging into your other modules
2. Create additional roles and permissions as needed
3. Add dashboard widgets as features are completed
4. Customize the styling to match your brand colors (settings available)
5. Add notification system for important events

## Common Tasks

### Add Activity Logging to Product Module

```php
use App\Services\ActivityLogger;

// In your ProductController
public function store(Request $request)
{
    $product = Product::create($request->validated());
    ActivityLogger::logCreated('Product', $product->id, $request->validated());
    return redirect()->back();
}

public function update(Request $request, Product $product)
{
    $oldData = $product->toArray();
    $product->update($request->validated());
    ActivityLogger::logUpdated('Product', $product->id, [
        'changes' => array_diff($request->validated(), $oldData)
    ]);
    return redirect()->back();
}
```

### Get Dashboard Statistics for Reports

```php
use App\Models\ActivityLog;

// Get usage stats
$logs = ActivityLog::whereBetween('created_at', [$from, $to])
    ->selectRaw('action, COUNT(*) as count')
    ->groupBy('action')
    ->get();
```

### Export Activity Report

Navigate to `/admin/logs/export/csv?from_date=2024-01-01&to_date=2024-12-31` to download CSV.

## Support & Documentation

See `ADMIN_DASHBOARD_DOCS.md` for complete documentation and API reference.
