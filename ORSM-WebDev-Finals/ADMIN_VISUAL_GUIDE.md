# Admin Dashboard - Visual Structure & Navigation

## Site Map

```
/admin/
├── dashboard (Main Hub)
│   ├── Statistics Overview
│   ├── Recent Orders
│   ├── Recent Activities
│   └── Quick Actions
│
├── users/ (User Management)
│   ├── index - List all users
│   ├── create - Add new user
│   └── edit - Edit user
│
├── settings/ (System Configuration)
│   └── edit - Manage all settings
│
└── logs/ (Activity Audit Trail)
    ├── index - View all logs
    ├── show - View log details
    └── export - Download as CSV
```

## Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────┐
│                    ADMIN DASHBOARD                              │
├─────────────────────────────────────────────────────────────────┤
│                    Header with Title                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────┐ │
│  │ Total Users  │ │ Total Orders │ │ Pending Ord. │ │ Revenue│ │
│  │     42       │ │     156      │ │      8       │ │ $12,5  │ │
│  └──────────────┘ └──────────────┘ └──────────────┘ └────────┘ │
│                                                                   │
│  ┌────────────────────────────┐  ┌─────────────────────────────┐ │
│  │   Recent Orders            │  │   Recent Activity Feed      │ │
│  ├────────────────────────────┤  ├─────────────────────────────┤ │
│  │ Order # │ Status  │ Amount │  │ User  │ Action  │ Time      │ │
│  │ #1234   │ Pending │ $125   │  │ Admin │ Created │ 2 hrs ago │ │
│  │ #1233   │ Shipped │ $89    │  │ John  │ Updated │ 5 hrs ago │ │
│  │ #1232   │ Pending │ $234   │  │ Admin │ Deleted │ 1 day ago │ │
│  └────────────────────────────┘  └─────────────────────────────┘ │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │              Quick Actions                                 │ │
│  ├────────────────────────────────────────────────────────────┤ │
│  │  [Manage Users] [Settings] [Logs] [Back to Dashboard]     │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## User Management Flow

```
USERS PAGE
├─ Search by name/email
├─ Filter by status (Active/Inactive)
└─ Users Table
   ├─ List all users with pagination
   ├─ Show: Name, Email, Role, Status
   └─ Actions for each user:
      ├─ Edit - Change name, email, role
      ├─ Deactivate - Disable account
      ├─ Activate - Re-enable account
      └─ Delete - Remove permanently

ADD USER FORM
├─ Full Name (required)
├─ Email (required, unique)
├─ Password (required, with strength requirements)
├─ Confirm Password (required)
├─ Role (admin, manager, customer)
└─ Active (checkbox)

EDIT USER FORM
├─ Full Name (editable)
├─ Email (editable, unique)
├─ Role (changeable)
├─ Active Status (toggleable)
└─ User metadata (created, last updated)
```

## Settings Management Flow

```
SETTINGS PAGE
├─ Shop Information
│  ├─ Shop Name
│  ├─ Description
│  ├─ Email
│  ├─ Phone
│  └─ Address
│
├─ Branding
│  ├─ Primary Color (color picker)
│  └─ Secondary Color (color picker)
│
└─ Financial Settings
   ├─ Currency (dropdown)
   └─ Tax Rate (%)
```

## Activity Logs Flow

```
LOGS PAGE
├─ Filters (all optional)
│  ├─ Action Type (dropdown)
│  ├─ Subject Type (dropdown)
│  ├─ User (dropdown)
│  ├─ From Date (date picker)
│  ├─ To Date (date picker)
│  └─ [Filter] [Reset]
│
├─ Logs Table
│  ├─ Date & Time
│  ├─ User
│  ├─ Action (colored badge)
│  ├─ Subject Type
│  ├─ IP Address
│  └─ View Details link
│
├─ Pagination (50 per page)
│
└─ Export CSV Button (respects current filters)

LOG DETAILS PAGE
├─ Activity ID
├─ User Information
├─ Action Type
├─ Date & Time
├─ Subject Type & ID
├─ IP Address
├─ User Agent
└─ Changes Made (JSON format)
```

## Database Schema Visualization

```
USERS TABLE
┌─────────────────────────────────────────┐
│ id (PK) | name | email | password | role│ is_active │ timestamps │
├─────────────────────────────────────────┤
│ 1       │ John │ j@e   │ hash... │admin│ 1         │ 2024-01-01 │
│ 2       │ Jane │ j@e   │ hash... │mgr  │ 1         │ 2024-01-02 │
│ 3       │ Bob  │ b@e   │ hash... │cust │ 0         │ 2024-01-03 │
└─────────────────────────────────────────┘

ACTIVITY_LOGS TABLE
┌────────────────────────────────────────────────────────┐
│id │user_id│action│subject_type│subject_id│changes│... │
├────────────────────────────────────────────────────────┤
│1  │1      │create│User       │2         │{...}  │... │
│2  │1      │update│Settings   │null      │{...}  │... │
│3  │2      │login │User       │2         │null   │... │
└────────────────────────────────────────────────────────┘

SYSTEM_SETTINGS TABLE
┌──────────────────────────────────────────┐
│id│key          │value    │type    │desc  │
├──────────────────────────────────────────┤
│1 │shop_name    │"MyStore"│string  │...   │
│2 │tax_rate     │15       │number  │...   │
└──────────────────────────────────────────┘
```

## User Roles & Permissions

```
ROLE HIERARCHY
└─ Admin
   └─ Can access everything
   └─ Full CRUD on all resources
   └─ Manage other admins
   └─ Configure system settings
   
└─ Manager
   └─ Can access most features
   └─ Limited user management
   └─ Cannot manage settings
   
└─ Customer
   └─ Can access customer features only
   └─ Cannot access admin panel
```

## Admin Route Structure

```
/admin/                          # Prefix for all admin routes
├─ GET  /dashboard               # View dashboard
├─ GET  /users                   # List users
├─ GET  /users/create            # Show add user form
├─ POST /users                   # Store new user
├─ GET  /users/{id}/edit         # Show edit user form
├─ PATCH/users/{id}              # Update user
├─ POST /users/{id}/deactivate   # Deactivate user
├─ POST /users/{id}/activate     # Activate user
├─ DELETE /users/{id}            # Delete user
├─ GET  /settings                # Show settings form
├─ PATCH /settings               # Update settings
├─ GET  /logs                    # List activity logs
├─ GET  /logs/{id}               # View log details
└─ GET  /logs/export/csv         # Export logs as CSV
```

## Data Flow Diagram

```
User Action
    │
    ▼
AdminController
    │
    ├─► Validate Input
    │
    ├─► Process Action
    │
    ├─► Update Database
    │
    ├─► Log Activity (ActivityLogger)
    │       │
    │       ▼
    │    Store in activity_logs
    │
    ├─► Return Response
    │
    ▼
User sees result + notification
```

## File Organization

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AdminController.php (1 file)
│   └── Middleware/
│       └── IsAdmin.php (1 file)
├── Models/
│   ├── User.php (updated)
│   ├── ActivityLog.php (new)
│   └── SystemSettings.php (new)
└── Services/
    └── ActivityLogger.php (new)

database/
└── migrations/
    ├── 2025_12_04_000001_create_activity_logs_table.php
    ├── 2025_12_04_000002_create_system_settings_table.php
    └── 2025_12_04_000003_add_role_and_active_to_users.php

resources/
└── views/
    └── admin/
        ├── dashboard.blade.php
        ├── users/
        │   ├── index.blade.php
        │   ├── create.blade.php
        │   └── edit.blade.php
        ├── settings/
        │   └── edit.blade.php
        └── logs/
            ├── index.blade.php
            └── show.blade.php

Documentation/
├── ADMIN_DASHBOARD_DOCS.md (comprehensive)
├── ADMIN_SETUP_GUIDE.md (quick start)
└── ADMIN_COMPLETION_SUMMARY.md (this project summary)
```

## Integration Points with Other Modules

```
┌──────────────────────────────────────────────────────┐
│          Admin Dashboard & System Settings           │
└──────────────────────────────────────────────────────┘
            ▲                      ▲
            │                      │
      ┌─────┴──────┐      ┌────────┴──────┐
      │            │      │               │
      ▼            ▼      ▼               ▼
 ┌────────┐  ┌────────┐ ┌───────┐  ┌──────────┐
 │Products│  │Orders  │ │Inventory│ │Customers│
 └────────┘  └────────┘ └───────┘  └──────────┘

Logs all CRUD operations on these modules
```

## Activity Types Tracked

```
✓ User Management
  - User created
  - User updated
  - User deleted
  - User deactivated
  - User activated
  - Role changed

✓ Admin Actions
  - Settings changed
  - Configuration updated

✓ System Events
  - User login
  - User logout

✓ Future Integration Points
  - Product CRUD
  - Order operations
  - Inventory updates
  - Payment processing
```

## Color Scheme (Tailwind CSS)

```
├─ Primary: Blue (#3498db)
├─ Success: Green (#27ae60)
├─ Warning: Yellow (#f39c12)
├─ Error: Red (#e74c3c)
├─ Info: Purple (#8e44ad)
└─ Neutral: Gray (#7f8c8d)

Dark Mode Support:
├─ Dark Background: #1f2937
├─ Dark Cards: #111827
└─ Dark Text: #f3f4f6
```

## Responsive Breakpoints

```
Mobile First Design:
├─ Mobile: < 640px
├─ Tablet: 768px - 1024px
├─ Desktop: > 1024px
└─ All tables are horizontally scrollable on mobile
```
