<?php

namespace sndpbag\DynamicRoles\Traits;

use sndpbag\DynamicRoles\Models\Role;
use sndpbag\DynamicRoles\Models\Permission;

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

    public function assignRole(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        if ($roles->isEmpty()) {
            return $this;
        }

        $this->roles()->syncWithoutDetaching($roles);
        return $this;
    }

    public function removeRole(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        $this->roles()->detach($roles);
        return $this;
    }

    public function syncRoles(...$roles)
    {
        $roles = $this->getAllRoles($roles);
        $this->roles()->sync($roles);
        return $this;
    }

    public function hasPermission($permission)
    {
        // Check if user is super admin
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_string($permission)) {
            return $this->hasPermissionViaRole($permission);
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($this->hasPermission($p)) {
                    return true;
                }
            }
            return false;
        }

        return $this->hasPermissionViaRole($permission);
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