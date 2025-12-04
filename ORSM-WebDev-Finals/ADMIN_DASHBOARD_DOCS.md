# Admin Dashboard & System Settings Documentation

## Overview

This module provides a complete Admin Dashboard and System Settings management system for the Online Retail Store Management System. It includes user management, system configuration, and comprehensive activity logging.

## Features

### 1. Admin Dashboard
- **Dashboard Overview**: Displays key metrics including:
  - Total users count
  - Total orders count
  - Pending orders count
  - Total revenue
  - Monthly revenue
  - Recent activity feed
  - Recent orders list
  - Quick action buttons

### 2. User Management
- **List Users**: View all users with pagination and search
- **Add User**: Create new users with role assignment (Admin, Manager, Customer)
- **Edit User**: Modify user information and roles
- **Deactivate/Activate**: Toggle user status without deletion
- **Delete User**: Permanently remove users
- **Role Management**: Assign different roles to users
- **Search & Filter**: Search by name/email, filter by status

### 3. System Settings
- **Shop Information**: Configure store name, description, email, phone, and address
- **Financial Settings**: Configure currency and tax rates
- **Settings Management**: Easy-to-use interface for updating system configuration

### 4. Activity Logs
- **Comprehensive Logging**: Track all user and admin actions
- **Filter & Search**: Filter logs by action, subject type, user, or date range
- **Export**: Export logs to CSV for analysis
- **Detailed View**: View full details of each logged activity
- **Change Tracking**: See what changes were made in each action

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `activity_logs` - Stores all activity records
- `system_settings` - Stores system configuration
- Updated `users` table with `role` and `is_active` columns

### 2. Update Routes

Routes are already configured in `routes/web.php`:

```
/admin/dashboard - Main admin dashboard
/admin/users - User management
/admin/settings - System settings
/admin/logs - Activity logs
```

### 3. Update Middleware (if needed)

The admin middleware is available in `app/Http/Middleware/IsAdmin.php`. You can add it to `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... other middleware
    'admin' => \App\Http\Middleware\IsAdmin::class,
];
```

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AdminController.php (Main admin controller)
│   └── Middleware/
│       └── IsAdmin.php (Admin authorization middleware)
├── Models/
│   ├── User.php (Updated with role and is_active)
│   ├── ActivityLog.php (Activity logging model)
│   └── SystemSettings.php (System configuration model)
└── Services/
    └── ActivityLogger.php (Logging service)

database/
├── migrations/
│   ├── 2025_12_04_000001_create_activity_logs_table.php
│   ├── 2025_12_04_000002_create_system_settings_table.php
│   └── 2025_12_04_000003_add_role_and_active_to_users.php

resources/views/admin/
├── dashboard.blade.php (Admin dashboard)
├── users/
│   ├── index.blade.php (User list)
│   ├── create.blade.php (Add user form)
│   └── edit.blade.php (Edit user form)
├── settings/
│   └── edit.blade.php (Settings form)
└── logs/
    ├── index.blade.php (Activity logs list)
    └── show.blade.php (Log details)
```

## Usage

### Using the Activity Logger

The `ActivityLogger` service class provides easy methods to log activities:

```php
use App\Services\ActivityLogger;

// Log user creation
ActivityLogger::logCreated('User', $user->id, ['name' => $user->name]);

// Log user update
ActivityLogger::logUpdated('User', $user->id, ['field' => 'email', 'old' => 'old@email.com', 'new' => 'new@email.com']);

// Log user deletion
ActivityLogger::logDeleted('User', $user->id);

// Log user deactivation
ActivityLogger::logUserDeactivated($user->id, 'Admin action');

// Log role change
ActivityLogger::logRoleChanged($user->id, 'customer', 'admin');

// Log settings change
ActivityLogger::logSettingsChanged('shop_name', 'Old Store', 'New Store');

// Get recent activities
$activities = ActivityLogger::getRecent(50);

// Get user's activities
$userActivities = ActivityLogger::getUserActivities($userId);
```

### Using System Settings

The `SystemSettings` model provides a simple API for managing settings:

```php
use App\Models\SystemSettings;

// Get a setting
$shopName = SystemSettings::get('shop_name', 'Default Store');

// Set a setting
SystemSettings::set('shop_name', 'My Awesome Store');

// Get all shop settings
$settings = SystemSettings::getShopSettings();

// Update multiple settings
SystemSettings::updateShopSettings([
    'shop_name' => 'New Name',
    'shop_email' => 'email@store.com',
]);

// Delete a setting
SystemSettings::forget('shop_logo');
```

### User Model Methods

The User model now includes helpful methods:

```php
$user = User::find(1);

// Check role
$user->isAdmin();      // Returns bool
$user->isManager();    // Returns bool
$user->isCustomer();   // Returns bool

// Get only active users
$activeUsers = User::active()->get();

// Get user's activity logs
$logs = $user->activityLogs()->get();
```

### Querying Activity Logs

The ActivityLog model includes convenient query scopes:

```php
use App\Models\ActivityLog;

// Get recent activities (default: 50)
$logs = ActivityLog::recent(10)->get();

// Filter by action
$creations = ActivityLog::byAction('create')->get();

// Filter by subject type
$userLogs = ActivityLog::bySubject('User')->get();

// Filter by user
$userActivities = ActivityLog::byUser($userId)->get();

// Date range
$logs = ActivityLog::dateBetween('2024-01-01', '2024-12-31')->get();

// Chaining
$logs = ActivityLog::byUser($userId)
    ->byAction('update')
    ->recent(20)
    ->get();
```

## Key Models

### ActivityLog Model

```php
- id: Integer (Primary Key)
- user_id: Foreign Key (User)
- action: String (create, update, delete, login, logout, etc.)
- subject_type: String (Model type being acted upon)
- subject_id: Integer (ID of the affected resource)
- changes: JSON (Stores before/after values)
- ip_address: String
- user_agent: String
- timestamps: created_at, updated_at
```

### SystemSettings Model

```php
- id: Integer (Primary Key)
- key: String (Unique setting key)
- value: Text (Setting value)
- type: String (Type: string, text, number, boolean, json)
- description: Text
- timestamps: created_at, updated_at
```

### User Model (Updated)

```php
- id: Integer (Primary Key)
- name: String
- email: String (Unique)
- password: String (Hashed)
- role: Enum (admin, manager, customer)
- is_active: Boolean
- timestamps: created_at, updated_at
```

## API Endpoints

### Dashboard
- `GET /admin/dashboard` - View admin dashboard

### User Management
- `GET /admin/users` - List users
- `GET /admin/users/create` - Create user form
- `POST /admin/users` - Store new user
- `GET /admin/users/{user}/edit` - Edit user form
- `PATCH /admin/users/{user}` - Update user
- `POST /admin/users/{user}/deactivate` - Deactivate user
- `POST /admin/users/{user}/activate` - Activate user
- `DELETE /admin/users/{user}` - Delete user

### System Settings
- `GET /admin/settings` - Edit settings form
- `PATCH /admin/settings` - Update settings

### Activity Logs
- `GET /admin/logs` - View activity logs
- `GET /admin/logs/{log}` - View log details
- `GET /admin/logs/export/csv` - Export logs as CSV

## Validation Rules

### User Creation/Update
- **name**: Required, string, max 255 characters
- **email**: Required, email, unique (except on update)
- **password**: Required on create, min 8 chars, uppercase, lowercase, number, special char
- **role**: Required, must be admin/manager/customer
- **is_active**: Boolean

### Settings Update
- **shop_name**: Required, string, max 255
- **shop_email**: Required, valid email
- **shop_phone**: Required, max 20 characters
- **currency**: Required, valid currency code
- **tax_rate**: Optional, numeric, 0-100

## Security Considerations

1. **Admin Middleware**: All admin routes are protected by authentication and authorization
2. **Input Validation**: All inputs are validated before processing
3. **CSRF Protection**: CSRF tokens are included in all forms
4. **Activity Logging**: All admin actions are logged for audit trail
5. **User Deactivation**: Users can be deactivated instead of deleted for data integrity
6. **Password Security**: Passwords are hashed using Laravel's security features

## Integration Notes

### With Order Management
The dashboard automatically checks for the Order model and loads order statistics if available. Once your team implements the Order model, these stats will populate automatically.

### With Inventory Management
When the Inventory system is implemented, low stock alerts can be added to the dashboard by modifying the `dashboard` method in AdminController.

### With Authentication
The system integrates with Laravel's built-in authentication. Ensure users are properly authenticated before accessing admin routes.

## Troubleshooting

### "Unauthorized" Error
- Ensure user has `role = 'admin'`
- Check that `is_active = true`
- Verify middleware is properly configured

### Activity Logs Not Appearing
- Ensure `ActivityLogger::log()` is called where needed
- Check that `activity_logs` table exists (run migrations)
- Verify user is authenticated when action is performed

### Settings Not Saving
- Check `system_settings` table exists
- Verify form POST data matches expected field names
- Look for validation errors in response

## Future Enhancements

1. **Bulk Operations**: Add bulk user actions
2. **Advanced Reports**: Create detailed analytics dashboards
3. **Email Notifications**: Notify admins of important events
4. **Role-Based Permissions**: Implement granular permissions
5. **Audit Trails**: Export detailed audit reports
6. **Dashboard Widgets**: Make dashboard customizable
7. **User Impersonation**: Allow admins to impersonate users for support

## Support

For questions or issues with the Admin Dashboard system, refer to the inline code documentation or contact the development team.
