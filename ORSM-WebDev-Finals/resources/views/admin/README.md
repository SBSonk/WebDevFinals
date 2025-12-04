# Admin Dashboard Module

## Overview

This folder contains all the Blade templates for the Admin Dashboard & System Settings module of the Online Retail Store Management System.

## Directory Structure

```
admin/
├── dashboard.blade.php      - Main admin dashboard
├── users/
│   ├── index.blade.php      - List all users
│   ├── create.blade.php     - Create new user form
│   └── edit.blade.php       - Edit user form
├── settings/
│   └── edit.blade.php       - System settings form
└── logs/
    ├── index.blade.php      - Activity logs list
    └── show.blade.php       - Activity log details
```

## Views Description

### Dashboard (`dashboard.blade.php`)
The main admin hub displaying:
- System statistics (users, orders, revenue)
- Recent orders table
- Recent activity feed
- Quick action buttons to other sections

**Route**: `/admin/dashboard`

### Users Management

#### index.blade.php
Displays a paginated list of all users with:
- Search by name/email
- Filter by active/inactive status
- Action buttons (Edit, Deactivate, Delete)
- User details (Name, Email, Role, Status)

**Route**: `/admin/users`

#### create.blade.php
Form for creating a new user with:
- Full name input
- Email input (with uniqueness)
- Password input (with strength validation)
- Password confirmation
- Role selection (Admin, Manager, Customer)
- Active status checkbox

**Route**: `/admin/users/create`
**Method**: POST to `/admin/users`

#### edit.blade.php
Form for editing an existing user with:
- Editable name and email
- Role selection
- Active status toggle
- Metadata display (created, updated times)

**Route**: `/admin/users/{user}/edit`
**Method**: PATCH to `/admin/users/{user}`

### Settings Management

#### edit.blade.php
Single form for all system settings with sections:

**Shop Information**
- Shop name
- Shop description
- Shop email
- Shop phone
- Shop address

**Branding**
- Primary color (with color picker)
- Secondary color (with color picker)

**Financial Settings**
- Currency selection (USD, EUR, GBP, CAD, AUD)
- Tax rate percentage

**Route**: `/admin/settings`
**Method**: PATCH to `/admin/settings`

### Activity Logs

#### index.blade.php
Displays paginated activity logs with:
- Filter by action type
- Filter by subject type
- Filter by user
- Filter by date range
- Sortable table with: Date, User, Action, Subject, IP, Action links
- Export to CSV button
- View details link for each log

**Route**: `/admin/logs`

#### show.blade.php
Detailed view of a single activity log showing:
- Activity ID
- User who performed action
- Action type (with colored badge)
- Date and time
- Subject type and ID
- IP address
- User agent
- Changes made (formatted JSON)

**Route**: `/admin/logs/{log}`

## Styling

All views use:
- **Tailwind CSS** for styling
- **Responsive design** with mobile-first approach
- **Dark mode support** with dark: prefix classes
- **Color-coded status badges** for easy identification
- **Accessible forms** with proper labels and validation

## Components Used

All views extend the `layouts/app.blade.php` layout which includes:
- Navigation menu
- Header section
- Flash messages
- Responsive container
- Dark mode toggle

## Form Features

### Flash Messages
- Success messages after actions
- Error messages with validation details
- Automatic dismissal or manual close

### Validation Display
- Inline error messages below fields
- Error summary at top of form
- Highlight invalid fields with red border

### CSRF Protection
- All forms include `@csrf` token
- PATCH requests include `@method('PATCH')`
- DELETE requests include `@method('DELETE')`

## JavaScript Features

- Color picker sync for branding settings
- Confirmation dialogs for destructive actions
- Form validation before submission
- Copy-to-clipboard for technical fields

## Accessibility Features

- Proper semantic HTML
- ARIA labels where needed
- Keyboard navigation support
- High contrast ratios
- Focus indicators on interactive elements

## Mobile Optimization

- Responsive grid layouts
- Horizontal scroll on tables for mobile
- Touch-friendly buttons and links
- Stack layout for forms on mobile
- Readable font sizes on all devices

## Dark Mode Support

All views support dark mode with:
- `dark:bg-gray-800` for backgrounds
- `dark:text-white` for text
- `dark:border-gray-600` for borders
- Proper contrast ratios maintained

## Performance

- Minimal inline styles
- CSS classes reused across components
- Lazy loading where applicable
- Optimized images and icons
- CSS inlining for critical styles

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Integration Notes

These views integrate with:
- `AdminController` for all logic
- `User` model for user data
- `ActivityLog` model for log data
- `SystemSettings` model for settings
- Laravel's built-in auth system

## Customization Guide

### Change Colors
1. Color customization removed from settings. Modify Tailwind classes directly in views if needed.

### Add New Fields
1. Add field to form in view
2. Add validation rule in controller
3. Update migration if new table column needed

### Modify Pagination
1. Update `paginate()` call in controller
2. Change number in controller method

### Change Table Columns
1. Edit table headers in view
2. Update table body loop to show desired columns
3. Update query in controller if needed

## Template Variables

### Dashboard
- `$stats` - Array with user/order/revenue counts
- `$recent_activities` - Collection of recent ActivityLog models
- `$recent_orders` - Collection of recent Order models
- `$low_stock_alerts` - Collection of low inventory items

### Users
- `$users` - Paginated collection of User models
- `$user` - Single User model for edit page

### Settings
- `$settings` - Array of all system settings

### Logs
- `$logs` - Paginated collection of ActivityLog models
- `$users` - Collection of users for filter dropdown
- `$actions` - Collection of distinct action types
- `$subjects` - Collection of distinct subject types

## Common Blade Syntax Reference

```blade
{{-- Conditional --}}
@if($condition)
    {{-- content --}}
@else
    {{-- content --}}
@endif

{{-- Loop --}}
@foreach($items as $item)
    {{-- content --}}
@empty
    {{-- no items --}}
@endforeach

{{-- Route Link --}}
{{ route('admin.users.index') }}

{{-- Auth Check --}}
@auth
    {{-- authenticated content --}}
@endauth
```

## Testing

To test these views:
1. Access `/admin/dashboard` - verify all sections load
2. Visit `/admin/users` - test search and filter
3. Go to `/admin/users/create` - test form validation
4. Visit `/admin/settings` - test settings update
5. Check `/admin/logs` - test log filtering and export

## Debugging

- Use `dd()` in blade for variable inspection
- Check browser console for JavaScript errors
- Review Laravel logs in `storage/logs/`
- Use Laravel Tinker to test queries

## Documentation

See main documentation files:
- `ADMIN_DASHBOARD_DOCS.md` - Complete reference
- `ADMIN_SETUP_GUIDE.md` - Setup instructions
- `ADMIN_QUICK_REFERENCE.md` - Developer reference

---

**Last Updated**: December 4, 2025
**Status**: Production Ready
