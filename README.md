<p align="center">
  <a href="https://packagist.org/packages/sndpbag/laravel-dynamic-roles">
    <img src="https://img.shields.io/packagist/v/sndpbag/laravel-dynamic-roles" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/sndpbag/laravel-dynamic-roles">
    <img src="https://img.shields.io/packagist/dt/sndpbag/laravel-dynamic-roles" alt="Total Downloads">
  </a>
  <a href="https://github.com/sndpbag/laravel-dynamic-roles/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/sndpbag/laravel-dynamic-roles" alt="License">
  </a>
</p>


# Laravel Dynamic Roles & Permissions

A comprehensive and easy-to-use Laravel package for managing roles and permissions dynamically. This package automatically syncs your application routes to permissions and provides a beautiful admin interface for managing roles, permissions, and user assignments.

 ## Features

- ✅ **Dynamic Permission Management** - Automatically sync all your routes to permissions
- ✅ **Role-Based Access Control (RBAC)** - Create and manage roles with ease
- ✅ **Role Hierarchy** - Supports parent-child relationships between roles
- ✅ **User Role Assignment** - Assign multiple roles to users
- ✅ **Direct User Permissions** - Assign permissions directly to users, bypassing roles
- ✅ **Beautiful Admin UI** - Pre-built Tailwind CSS interface
- ✅ **Middleware Support** - Protect routes with role and permission middleware
- ✅ **Flexible Configuration** - Customize everything via config file
- ✅ **Group Permissions** - Organize permissions by groups
- ✅ **Super Admin Support** - Built-in super admin role with full access
- ✅ **Easy Installation** - One command setup

## Requirements

- PHP 8.1+
- Laravel 11.x or 12.x

## Installation

### Step 1: Install via Composer

```bash
composer require sndpbag/laravel-dynamic-roles
```

```bash
   composer require sndpbag/laravel-dynamic-roles:dev-main
```

### Step 2: Run Installation Command

```bash
php artisan dynamic-roles:install
```

This command will:
- Publish configuration files
- Publish views
- Run migrations
- Create default roles (Super Admin, Admin, User)
- Sync existing routes to permissions

### Step 3: Add Trait to User Model

Add the `HasRolesAndPermissions` trait to your `User` model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use sndpbag\DynamicRoles\Traits\HasRolesAndPermissions;

class User extends Authenticatable
{
    use HasRolesAndPermissions;
    
    // ... rest of your User model
}
```

## Usage

### Accessing Admin Panel

Visit `/admin/roles-permissions` in your browser to access the admin panel.

### Protecting Routes with Middleware

#### By Role

```php
// Single role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
});

// Multiple roles (user needs ANY of these roles)
Route::middleware(['auth', 'role:admin,moderator'])->group(function () {
    Route::get('/moderation', [ModerationController::class, 'index']);
});
```

#### By Permission

```php
// Single permission
Route::middleware(['auth', 'permission:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});

// Multiple permissions (user needs ANY of these permissions)
Route::middleware(['auth', 'permission:posts.edit,posts.delete'])->group(function () {
    Route::put('/posts/{post}', [PostController::class, 'update']);
});
```

### Blade Directives

Check roles and permissions in your Blade templates:

```blade
@if(auth()->user()->hasRole('admin'))
    <p>You are an admin!</p>
@endif

@if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('users.create') }}">Create User</a>
@endif

@if(auth()->user()->hasAnyRole(['admin', 'moderator']))
    <p>You have administrative access</p>
@endif

@if(auth()->user()->hasAllRoles(['admin', 'super-admin']))
    <p>You have both roles</p>
@endif
```

### Programmatic Usage

#### Assigning Roles

```php
$user = User::find(1);

// Assign single role
$user->assignRole('admin');

// Assign multiple roles
$user->assignRole('admin', 'moderator');

// Assign by ID
$user->assignRole(1);

// Remove role
$user->removeRole('admin');

// Sync roles (removes all other roles)
$user->syncRoles('admin', 'moderator');
```

#### Checking Roles

```php
// Check single role
if ($user->hasRole('admin')) {
    // User is admin
}

// Check multiple roles (has ANY)
if ($user->hasAnyRole(['admin', 'moderator'])) {
    // User has at least one of these roles
}

// Check multiple roles (has ALL)
if ($user->hasAllRoles(['admin', 'moderator'])) {
    // User has all these roles
}

// Check if super admin
if ($user->isSuperAdmin()) {
    // User is super admin
}
```

#### Checking Permissions

```php
// Check single permission
if ($user->hasPermission('users.create')) {
    // User can create users
}

// Check multiple permissions (has ANY)
if ($user->hasAnyPermission(['users.create', 'users.edit'])) {
    // User has at least one of these permissions
}

// Check multiple permissions (has ALL)
if ($user->hasAllPermissions(['users.create', 'users.edit'])) {
    // User has all these permissions
}
```

#### Managing Role Permissions

```php
$role = Role::find(1);

// Give permission to role
$role->givePermissionTo('users.create');

// Give multiple permissions
$role->givePermissionTo('users.create', 'users.edit', 'users.delete');

// Revoke permission
$role->revokePermissionTo('users.delete');

// Sync permissions (removes all other permissions)
$role->syncPermissions('users.create', 'users.edit');

// Check if role has permission
if ($role->hasPermission('users.create')) {
    // Role has this permission
}
```


#### Managing User Permissions  

You can also assign permissions directly to a user.

```php
$user = User::find(1);

// Give direct permission to user
$user->givePermissionTo('posts.create');

// Give multiple direct permissions
$user->givePermissionTo('posts.create', 'posts.edit');

// Revoke direct permission
$user->revokePermissionTo('posts.edit');

// Sync direct permissions (removes all other direct permissions)
$user->syncPermissions('posts.create', 'posts.delete');


### Syncing Routes to Permissions

Automatically sync all your application routes to permissions:

```bash
php artisan dynamic-roles:sync-routes
```

Or use the "Sync Routes" button in the admin panel.

### Artisan Commands

```bash
# Install the package
php artisan dynamic-roles:install

# Sync routes to permissions
php artisan dynamic-roles:sync-routes
```

## Configuration

Publish and customize the configuration file:

```bash
php artisan vendor:publish --tag=dynamic-roles-config
```

Configuration options in `config/dynamic-roles.php`:

```php
return [
    // Customize table names
    'table_names' => [
       'users' => 'users',
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_permission' => 'role_permission',
        'user_role' => 'user_role',
        'user_permission' => 'user_permission',
    ],

    // Your User model
    'user_model' => \App\Models\User::class,

    // Admin panel route prefix
    'route_prefix' => 'admin/roles-permissions',

    // Middleware for admin routes
    'middleware' => ['web', 'auth','role:super-admin'],

    // Super admin role slug
    'super_admin_role' => 'super-admin',

    // Routes to exclude from permission sync
    'exclude_routes' => [
      'login',
        'logout',
        'register',
        'password.*',
        'sanctum.*',
        '_ignition.*',
    ],

    // Permission groups for organization
    'permission_groups' => [
      'users' => 'User Management',
        'roles' => 'Role Management',
        'permissions' => 'Permission Management',
        'posts' => 'Post Management',
        'categories' => 'Category Management',
    ],
];
```

## Customizing Views

Publish views to customize the admin interface:

```bash
php artisan vendor:publish --tag=dynamic-roles-views
```

Views will be published to `resources/views/vendor/dynamic-roles/`

## Database Structure

The package creates five tables:

- `roles` - Stores roles (includes `parent_id` for hierarchy)
- `permissions` - Stores permissions
- `role_permission` - Pivot table for role-permission relationships
- `user_role` - Pivot table for user-role relationships
- `user_permission` - Pivot table for user-direct-permission relationships

## Example Workflow

1. **Install the package** and run migrations
2. **Sync routes** to automatically create permissions from your routes
3. **Create roles** via admin panel or programmatically
4. **Assign permissions** to roles
5. **Assign roles** to users
6. **Protect routes** using middleware
7. **Check permissions** in your controllers or views

## Security

- Super admin role has access to everything by default.
- Permissions are checked via roles *and* direct user assignments.
- Middleware protects routes automatically.
- All database queries use Eloquent relationships.

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Credits

- **Author**: sndp bag (sandipan kr bag)
- **GitHub**: [https://github.com/sndpbag/laravel-roles-packege.git](https://github.com/sndpbag/laravel-roles-packege.git)

## Support

For issues and questions, please use the [GitHub Issues](https://github.com/sndpbag/laravel-roles-packege.git/issues) page.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.