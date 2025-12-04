<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Log an activity
     */
    public static function log(
        string $action,
        ?string $subjectType = null,
        mixed $subjectId = null,
        ?array $changes = null
    ) {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'changes' => $changes,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to prevent disrupting main application flow
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Log a login activity
     */
    public static function logLogin()
    {
        self::log('login', 'User', Auth::id());
    }

    /**
     * Log a logout activity
     */
    public static function logLogout()
    {
        self::log('logout', 'User', Auth::id());
    }

    /**
     * Log a resource creation
     */
    public static function logCreated(string $subjectType, mixed $subjectId, ?array $data = null)
    {
        self::log('create', $subjectType, $subjectId, $data);
    }

    /**
     * Log a resource update
     */
    public static function logUpdated(string $subjectType, mixed $subjectId, ?array $changes = null)
    {
        self::log('update', $subjectType, $subjectId, $changes);
    }

    /**
     * Log a resource deletion
     */
    public static function logDeleted(string $subjectType, mixed $subjectId, ?array $data = null)
    {
        self::log('delete', $subjectType, $subjectId, $data);
    }

    /**
     * Log user deactivation
     */
    public static function logUserDeactivated(mixed $userId, ?string $reason = null)
    {
        self::log('deactivate', 'User', $userId, $reason ? ['reason' => $reason] : null);
    }

    /**
     * Log user activation
     */
    public static function logUserActivated(mixed $userId)
    {
        self::log('activate', 'User', $userId);
    }

    /**
     * Log role change
     */
    public static function logRoleChanged(mixed $userId, mixed $oldRole, mixed $newRole)
    {
        self::log('update', 'User', $userId, [
            'field' => 'role',
            'old_value' => $oldRole,
            'new_value' => $newRole,
        ]);
    }

    /**
     * Log settings change
     */
    public static function logSettingsChanged(string $settingKey, mixed $oldValue, mixed $newValue)
    {
        self::log('update', 'SystemSettings', null, [
            'key' => $settingKey,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Get recent activities
     */
    public static function getRecent(int $limit = 50)
    {
        return ActivityLog::recent($limit)->with('user')->get();
    }

    /**
     * Get user's activities
     */
    public static function getUserActivities(mixed $userId, int $limit = 50)
    {
        return ActivityLog::byUser($userId)->recent($limit)->get();
    }

    /**
     * Get activities by subject type
     */
    public static function getBySubject(string $subjectType, int $limit = 50)
    {
        return ActivityLog::bySubject($subjectType)->recent($limit)->get();
    }
}
