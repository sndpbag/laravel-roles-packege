<?php

namespace Sndpbag\DynamicRoles\Traits;
use Illuminate\Support\Collection;
use Sndpbag\DynamicRoles\Models\Role;
use Sndpbag\DynamicRoles\Models\Permission;
use Illuminate\Support\Facades\Cache; 

trait HasRolesAndPermissions
{
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            config('dynamic-roles.table_names.user_role', 'user_role'),
            'user_id',
            'role_id'
        );
    }

    public function directPermissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('dynamic-roles.table_names.user_permission', 'user_permission'),
            'user_id',
            'permission_id'
        );
    }

    /**
     * Get all permissions via roles.
     */
    public function permissions()
    {
        // This method might be complex to define perfectly with hasManyThrough
        // It's better to rely on getAllPermissionsFromCache()
        // We keep it for potential simpler queries, but logic relies on the cache method.
        return $this->getAllPermissionsFromCache();
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }

        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
            return false;
        }

        return $this->roles->contains($role);
    }

    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    public function hasAllRoles($roles)
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    public function assignRole(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        if ($roles->isEmpty()) {
            return $this;
        }

        $this->roles()->syncWithoutDetaching($roles);
        $this->clearPermissionsCache(); // ক্যাশ ক্লিয়ার করুন

        return $this;
    }


    public function removeRole(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        $this->roles()->detach($roles);
        $this->clearPermissionsCache(); // ক্যাশ ক্লিয়ার করুন

        return $this;
    }


    public function syncRoles(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        $this->roles()->sync($roles);
        $this->clearPermissionsCache(); // ক্যাশ ক্লিয়ার করুন

        return $this;
    }

    public function hasPermission($permission)
    {
        // Super admin-এর জন্য ক্যাশ চেক করার দরকার নেই
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // ব্যবহারকারীর সমস্ত পারমিশন (ক্যাশ থেকে) পান
        $permissions = $this->getAllPermissionsFromCache();

        if (is_string($permission)) {
            return $permissions->contains('slug', $permission);
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                // Handle both slug strings and Permission objects
                $slug = is_string($p) ? $p : ($p->slug ?? null);
                if ($slug && $permissions->contains('slug', $slug)) {
                    return true;
                }
            }
            return false;
        }

        if ($permission instanceof Permission) {
             return $permissions->contains('slug', $permission->slug);
        }
        
        return false;
    }



    public function hasAnyPermission($permissions)
    {
        return $this->hasPermission($permissions);
    }

    public function hasAllPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for the user (from roles and direct), from cache.
     * এটি ঠিক করা হয়েছে
     */
    protected function getAllPermissionsFromCache()
    {
        $cacheKey = 'permissions_for_user_' . $this->id;
        $cacheDuration = config('dynamic-roles.cache_duration', 60);

        return Cache::remember($cacheKey, $cacheDuration, function () {
            
            // 1. রোল থেকে সমস্ত পারমিশন (হায়ারার্কি সহ) পান
            $rolePermissions = collect();
            foreach ($this->roles as $role) {
                $rolePermissions = $rolePermissions->merge($role->getAllPermissions());
            }

            // 2. সরাসরি পারমিশন পান
            $directPermissions = $this->directPermissions;

            // 3. দুটিকে একত্রিত করুন এবং ইউনিক করুন
            return $rolePermissions->merge($directPermissions)->unique('id');
        });
    }

    public function clearPermissionsCache()
    {
        Cache::forget('permissions_for_user_' . $this->id);
    }

    /**
     * This method is less efficient and redundant now,
     * as hasPermission() uses the cached 'getAllPermissionsFromCache'
     */
    protected function hasPermissionViaRole($permission)
    {
        // This logic is now handled by getAllPermissionsFromCache
        return $this->hasPermission($permission);
    }

    public function isSuperAdmin()
    {
        $superAdminRole = config('dynamic-roles.super_admin_role', 'super-admin');
        return $this->hasRole($superAdminRole);
    }

    protected function getAllRoles($roles)
    {
        return collect($roles)
            ->flatten()
            ->map(function ($role) {
                if ($role instanceof Role) {
                    return $role;
                }
                return Role::where('slug', $role)->orWhere('id', $role)->first();
            })
            ->filter()
            ->unique('id');
    }

    // --- নতুন যোগ করা মেথড ---

    /**
     * Assign direct permissions to the user.
     */
    public function givePermissionTo(...$permissions)
    {
        $permissions = $this->getPermissionModels($permissions);
        $this->directPermissions()->syncWithoutDetaching($permissions);
        $this->clearPermissionsCache();
        return $this;
    }

    /**
     * Revoke direct permissions from the user.
     */
    public function revokePermissionTo(...$permissions)
    {
        $permissions = $this->getPermissionModels($permissions);
        $this->directPermissions()->detach($permissions);
        $this->clearPermissionsCache();
        return $this;
    }

    /**
     * Sync direct permissions for the user.
     * এটি UserRoleController-এর জন্য প্রয়োজন
     */
    public function syncPermissions(...$permissions)
    {
        $permissions = $this->getPermissionModels($permissions);
        $this->directPermissions()->sync($permissions);
        $this->clearPermissionsCache();
        return $this;
    }

    /**
     * Helper to get permission models from mixed input.
     */
    protected function getPermissionModels($permissions): Collection
    {
        return collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if ($permission instanceof Permission) return $permission;
                return Permission::where('slug', $permission)->orWhere('id', $permission)->first();
            })
            ->filter()
            ->unique('id');
    }
}