<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SystemSettings;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware applied via routes/web.php group
    }

    /**
     * Display the admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_orders' => 0, // Will be populated when Order model is available
            'pending_orders' => 0, // Will be populated when Order model is available
            'total_revenue' => 0, // Will be populated when Order model is available
            'this_month_revenue' => 0, // Will be populated when Order model is available
        ];

        // Attempt to load order stats if the Order model and table exist
        try {
            if (class_exists('\App\Models\Order') && Schema::hasTable('orders')) {
                /** @var \App\Models\Order $orderClass */
                $orderClass = '\App\Models\Order';
                $stats['total_orders'] = $orderClass::count();
                $stats['pending_orders'] = $orderClass::where('order_status', 'pending')->count();
                $stats['total_revenue'] = $orderClass::where('payment_status', 'paid')->sum('total_amount');
                $stats['this_month_revenue'] = $orderClass::where('payment_status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount');
            }
        } catch (\Exception $e) {
            // Silent fail - Order model/table not ready yet
        }

        $recent_activities = ActivityLog::recent(10)->with('user')->get();
        
        $recent_orders = [];
        try {
            if (class_exists('\App\Models\Order') && Schema::hasTable('orders')) {
                /** @var \App\Models\Order $orderClass */
                $orderClass = '\App\Models\Order';
                $recent_orders = $orderClass::latest('created_at')->limit(5)->get();
            }
        } catch (\Exception $e) {
            // Silent fail - Order model/table not ready yet
        }
        
        // Low stock alerts (assuming inventory table exists)
        $low_stock_alerts = [];

        return view('admin.dashboard', compact('stats', 'recent_activities', 'recent_orders', 'low_stock_alerts'));
    }

    /**
     * Display all users for management
     */
    public function listUsers(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        // Filter by active/inactive
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $users = $query->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show form for adding new user
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,manager,customer'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        ActivityLogger::logCreated('User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully!');
    }

    /**
     * Show user edit form
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user information
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,manager,customer'],
            'is_active' => ['boolean'],
        ]);

        $oldRole = $user->role;
        $oldEmail = $user->email;

        $user->update($validated);

        // Log role changes
        if ($oldRole !== $validated['role']) {
            ActivityLogger::logRoleChanged($user->id, $oldRole, $validated['role']);
        }

        // Log email changes
        if ($oldEmail !== $validated['email']) {
            ActivityLogger::logUpdated('User', $user->id, [
                'field' => 'email',
                'old_value' => $oldEmail,
                'new_value' => $validated['email'],
            ]);
        }

        return redirect()->route('admin.users.index')
                        ->with('success', 'User updated successfully!');
    }

    /**
     * Deactivate a user
     */
    public function deactivateUser(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()
                            ->with('error', 'You cannot deactivate your own account!');
        }

        $user->update(['is_active' => false]);

        ActivityLogger::logUserDeactivated($user->id, 'Admin deactivated user');

        return redirect()->route('admin.users.index')
                        ->with('success', 'User deactivated successfully!');
    }

    /**
     * Activate a user
     */
    public function activateUser(User $user)
    {
        $user->update(['is_active' => true]);

        ActivityLogger::logUserActivated($user->id);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User activated successfully!');
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()
                            ->with('error', 'You cannot delete your own account!');
        }

        $userId = $user->id;
        $user->delete();

        ActivityLogger::logDeleted('User', $userId, ['name' => $user->name, 'email' => $user->email]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User deleted successfully!');
    }

    /**
     * Display system settings page
     */
    public function editSettings()
    {
        $settings = SystemSettings::getShopSettings();
        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'shop_description' => ['nullable', 'string', 'max:1000'],
            'shop_email' => ['required', 'email'],
            'shop_phone' => ['required', 'string', 'max:20'],
            'shop_address' => ['required', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSettings::get($key);
            
            if ($oldValue !== $value) {
                SystemSettings::set($key, $value);
                ActivityLogger::logSettingsChanged($key, $oldValue, $value);
            }
        }

        return redirect()->route('admin.settings.edit')
                        ->with('success', 'Settings updated successfully!');
    }

    /**
     * Display activity logs
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::query();

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->with('user')->latest('created_at')->paginate(50);
        $users = User::select('id', 'name', 'email')->get();
        $actions = ActivityLog::distinct('action')->pluck('action');
        $subjects = ActivityLog::distinct('subject_type')->pluck('subject_type');

        return view('admin.logs.index', compact('logs', 'users', 'actions', 'subjects'));
    }

    /**
     * Show activity log details
     */
    public function viewLogDetails(ActivityLog $log)
    {
        return view('admin.logs.show', compact('log'));
    }

    /**
     * Export activity logs to CSV
     */
    public function exportLogs(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->with('user')->latest('created_at')->get();

        $csv = "ID,User,Action,Subject Type,Subject ID,IP Address,Created At\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->user?->name ?? 'System',
                $log->action,
                $log->subject_type ?? 'N/A',
                $log->subject_id ?? 'N/A',
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity_logs_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
