# Admin Dashboard - Developer Quick Reference

## Quick Commands

```bash
# Run migrations
php artisan migrate

# Create admin user (interactive)
php artisan tinker
>>> App\Models\User::create(['name'=>'Admin', 'email'=>'admin@test.com', 'password'=>bcrypt('pass'), 'role'=>'admin', 'is_active'=>true])

# View logs in development
php artisan tail

# Clear cache
php artisan cache:clear
```

## Model Quick Reference

### User Model
```php
$user = User::find(1);
$user->isAdmin();              // Check role
$user->isManager();
$user->isCustomer();
$user->activityLogs();         // Get user's activities
$user->role;                   // Get role string
$user->is_active;              // Check if active

User::active()->get();         // Get only active users
```

### ActivityLog Model
```php
// Query examples
ActivityLog::recent(50)->get();                    // Last 50 activities
ActivityLog::byUser($userId)->get();               // User's activities
ActivityLog::byAction('create')->get();            // Create actions only
ActivityLog::bySubject('User')->get();             // Actions on Users
ActivityLog::dateBetween($from, $to)->get();       // Date range

// Get related user
$log->user->name;              // Get who did the action
$log->getDescriptionAttribute(); // Get human-readable action
$log->changes;                 // Get JSON changes
```

### SystemSettings Model
```php
// Get settings
$value = SystemSettings::get('shop_name');
$value = SystemSettings::get('shop_name', 'default');

// Set settings
SystemSettings::set('shop_name', 'My Store');
SystemSettings::set('tax_rate', 15, 'number');

// Bulk operations
$settings = SystemSettings::getShopSettings();
SystemSettings::updateShopSettings(['shop_name' => 'New Name']);

// Delete setting
SystemSettings::forget('shop_logo');
```

## Service Quick Reference

### ActivityLogger Service
```php
use App\Services\ActivityLogger;

// Log operations
ActivityLogger::log('create', 'Product', $id, $data);
ActivityLogger::logCreated('Product', $id, ['name' => 'Item']);
ActivityLogger::logUpdated('Product', $id, ['status' => 'active']);
ActivityLogger::logDeleted('Product', $id);

// Specific events
ActivityLogger::logLogin();
ActivityLogger::logLogout();
ActivityLogger::logUserDeactivated($userId);
ActivityLogger::logUserActivated($userId);
ActivityLogger::logRoleChanged($userId, 'customer', 'admin');
ActivityLogger::logSettingsChanged('shop_name', 'Old', 'New');

// Query methods
ActivityLogger::getRecent(50);
ActivityLogger::getUserActivities($userId);
ActivityLogger::getBySubject('Order');
```

## Controller Methods Reference

### AdminController Methods
```php
// Dashboard
public function dashboard()

// User Management
public function listUsers(Request $request)
public function createUser()
public function storeUser(Request $request)
public function editUser(User $user)
public function updateUser(Request $request, User $user)
public function deactivateUser(User $user)
public function activateUser(User $user)
public function deleteUser(User $user)

// Settings
public function editSettings()
public function updateSettings(Request $request)

// Logs
public function activityLogs(Request $request)
public function viewLogDetails(ActivityLog $log)
public function exportLogs(Request $request)
```

## Route Quick Reference

```php
// All routes are under /admin prefix
// Method | Path                    | Name                   | Action
GET    | /dashboard              | admin.dashboard        | Dashboard
GET    | /users                  | admin.users.index      | List users
GET    | /users/create           | admin.users.create     | Create form
POST   | /users                  | admin.users.store      | Store user
GET    | /users/{id}/edit        | admin.users.edit       | Edit form
PATCH  | /users/{id}             | admin.users.update     | Update user
POST   | /users/{id}/deactivate  | admin.users.deactivate | Deactivate
POST   | /users/{id}/activate    | admin.users.activate   | Activate
DELETE | /users/{id}             | admin.users.delete     | Delete
GET    | /settings               | admin.settings.edit    | Settings form
PATCH  | /settings               | admin.settings.update  | Update settings
GET    | /logs                   | admin.logs.index       | View logs
GET    | /logs/{id}              | admin.logs.show        | Log details
GET    | /logs/export/csv        | admin.logs.export      | Export CSV
```

## Blade Template Snippets

### Check if admin
```blade
@if(auth()->user()->isAdmin())
    {{-- Admin content --}}
@endif
```

### Show role badge
```blade
<span class="badge @if($user->role === 'admin') badge-danger @endif">
    {{ ucfirst($user->role) }}
</span>
```

### Active status display
```blade
@if($user->is_active)
    <span class="badge badge-success">Active</span>
@else
    <span class="badge badge-secondary">Inactive</span>
@endif
```

### Form errors
```blade
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </div>
@endif
```

### CSRF token
```blade
@csrf
```

### Route links
```blade
<a href="{{ route('admin.dashboard') }}">Dashboard</a>
<a href="{{ route('admin.users.index') }}">Users</a>
<a href="{{ route('admin.settings.edit') }}">Settings</a>
<a href="{{ route('admin.logs.index') }}">Logs</a>
```

## Common Validation Rules

```php
// User validation
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users,email,' . $user->id],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
    'role' => ['required', 'in:admin,manager,customer'],
    'is_active' => ['boolean'],
]);

// Settings validation
$validated = $request->validate([
    'shop_name' => ['required', 'string', 'max:255'],
    'shop_email' => ['required', 'email'],
    'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
]);
```

## Database Query Patterns

```php
// Get users by role
$admins = User::where('role', 'admin')->get();

// Get active users
$active = User::where('is_active', true)->get();

// Count users by role
$counts = User::selectRaw('role, COUNT(*) as count')
    ->groupBy('role')
    ->get();

// Get recent activity
$logs = ActivityLog::where('created_at', '>=', now()->subDay())
    ->latest()
    ->get();

// Search with multiple fields
$search = 'john';
$users = User::where('name', 'like', "%{$search}%")
    ->orWhere('email', 'like', "%{$search}%")
    ->get();
```

## Testing Quick Reference

```php
// Create test user
$user = User::factory()->create(['role' => 'admin']);

// Login
$this->actingAs($user);

// Test route
$response = $this->get('/admin/dashboard');
$response->assertStatus(200);

// Check view data
$response->assertViewHas('stats');
```

## Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| 403 Unauthorized | Not admin user | Ensure `role='admin'` and `is_active=true` |
| Migration error | Already ran | Check migrations directory |
| Model not found | Wrong namespace | Use full namespace: `\App\Models\User` |
| View not found | Wrong path | Check view path in resources/views |
| Route not found | Not registered | Verify routes in web.php |
| CSRF mismatch | Missing token | Add `@csrf` in form |
| Validation fails | Invalid input | Check validation rules |
| Settings not saved | Missing field | Ensure all required fields in form |

## Performance Tips

```php
// Eager load relationships
ActivityLog::with('user')->get();

// Use pagination
User::paginate(15);

// Add indexes to frequently searched columns
// Already done in migrations for activity_logs

// Cache settings
$settings = Cache::remember('system_settings', 3600, function() {
    return SystemSettings::getShopSettings();
});

// Limit recent logs
ActivityLog::recent(50)->get();
```

## Security Checklist

- ✓ All admin routes require `auth` middleware
- ✓ CSRF tokens in all forms
- ✓ Input validation on all endpoints
- ✓ Activity logging for audit trail
- ✓ User deactivation instead of deletion
- ✓ Role-based access control
- ✓ Password hashing for users
- ✓ Error messages don't expose sensitive info

## Debugging Tips

```php
// Check authenticated user
dd(auth()->user());

// Log activities manually
Log::info('Debug message', ['user' => auth()->id()]);

// Check database
DB::enableQueryLog();
// ... perform queries ...
dd(DB::getQueryLog());

// Verify settings
dd(SystemSettings::getShopSettings());

// Check activity logs
dd(ActivityLog::recent(10)->with('user')->get());
```

## File Locations Quick Map

```
AdminController     → app/Http/Controllers/AdminController.php
Models              → app/Models/ (User, ActivityLog, SystemSettings)
Service             → app/Services/ActivityLogger.php
Middleware          → app/Http/Middleware/IsAdmin.php
Migrations          → database/migrations/2025_12_04_*
Dashboard View      → resources/views/admin/dashboard.blade.php
User Views          → resources/views/admin/users/*.blade.php
Settings View       → resources/views/admin/settings/edit.blade.php
Logs Views          → resources/views/admin/logs/*.blade.php
Routes              → routes/web.php
Documentation       → ADMIN_*.md files
```

## Important Constants

```php
// User Roles
'admin'     // Full access
'manager'   // Limited access
'customer'  // Customer-only features

// Activity Actions
'create'    // Resource created
'update'    // Resource updated
'delete'    // Resource deleted
'login'     // User logged in
'logout'    // User logged out
'view'      // Resource viewed
'activate'  // Resource activated
'deactivate'// Resource deactivated

// Pagination
15          // Users per page
50          // Activity logs per page
```

## Useful Artisan Commands

```bash
php artisan make:migration create_table_name
php artisan make:model ModelName
php artisan make:controller ControllerName
php artisan make:middleware MiddlewareName
php artisan make:seeder SeederName
php artisan migrate:rollback
php artisan migrate:fresh
php artisan tinker
php artisan db:seed --class=SeederName
```

## Next Steps for Integration

1. **Into Product Module**: `ActivityLogger::logCreated('Product', $id, data)`
2. **Into Order Module**: `ActivityLogger::logCreated('Order', $id, data)`
3. **Into Inventory**: `ActivityLogger::logUpdated('Inventory', $id, data)`
4. **Into Checkout**: `ActivityLogger::logCreated('Order', $id, data)`
5. **Into Authentication**: Add login/logout logging

---

**Last Updated**: December 4, 2025
**Version**: 1.0
**Status**: Production Ready
