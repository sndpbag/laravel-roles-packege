<?php

namespace sndpbag\DynamicRoles\Traits;
use Illuminate\Support\Collection;
use sndpbag\DynamicRoles\Models\Role;
use sndpbag\DynamicRoles\Models\Permission;
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

    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            Role::class,
            'user_role.user_id',
            'role_permission.permission_id',
            'id',
            'user_role.role_id'
        )->distinct();
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

    // public function assignRole(...$roles)
    // {
    //     $roles = $this->getAllRoles($roles);
    //     if ($roles->isEmpty()) {
    //         return $this;
    //     }

    //     $this->roles()->syncWithoutDetaching($roles);
    //     return $this;
    // }

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


    // public function removeRole(...$roles)
    // {
    //     $roles = $this->getAllRoles($roles);
    //     $this->roles()->detach($roles);
    //     return $this;
    // }

        public function removeRole(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        $this->roles()->detach($roles);
        $this->clearPermissionsCache(); // ক্যাশ ক্লিয়ার করুন

        return $this;
    }


    // public function syncRoles(...$roles)
    // {
    //     $roles = $this->getAllRoles($roles);
    //     $this->roles()->sync($roles);
    //     return $this;
    // }

        public function syncRoles(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        $this->roles()->sync($roles);
        $this->clearPermissionsCache(); // ক্যাশ ক্লিয়ার করুন

        return $this;
    }

    // public function hasPermission($permission)
    // {
    //     // Check if user is super admin
    //     if ($this->isSuperAdmin()) {
    //         return true;
    //     }

    //     if (is_string($permission)) {
    //         return $this->hasPermissionViaRole($permission);
    //     }

    //     if (is_array($permission)) {
    //         foreach ($permission as $p) {
    //             if ($this->hasPermission($p)) {
    //                 return true;
    //             }
    //         }
    //         return false;
    //     }

    //     return $this->hasPermissionViaRole($permission);
    // }

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
                if ($permissions->contains('slug', $p)) {
                    return true;
                }
            }
            return false;
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

    protected function getAllPermissionsFromCache()
    {
        $cacheKey = 'permissions_for_user_' . $this->id;
        $cacheDuration = config('dynamic-roles.cache_duration', 60); // config থেকে সময় নিন (ডিফল্ট ৬০ মিনিট)

        return Cache::remember($cacheKey, $cacheDuration, function () {
            // যদি ক্যাশে না থাকে, ডেটাবেস থেকে আনুন
            $permissions = collect();
            foreach ($this->roles()->with('permissions')->get() as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
            return $permissions->unique('id');
        });
    }

    public function clearPermissionsCache()
    {
        Cache::forget('permissions_for_user_' . $this->id);
    }

    protected function hasPermissionViaRole($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
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
}