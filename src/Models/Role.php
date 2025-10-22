<?php

namespace Sndpbag\DynamicRoles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('dynamic-roles.table_names.roles', 'roles'));
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    // Role Hierarchy Relationships
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    // Permissions of this role only
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('dynamic-roles.table_names.role_permission', 'role_permission'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all permissions of the role, including inherited ones from parents.
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;
        if ($this->parent) {
            $permissions = $permissions->merge($this->parent->getAllPermissions());
        }
        return $permissions->unique('id');
    }
    
    public function hasPermission($permission): bool
    {
        $permissionSlug = is_string($permission) ? $permission : $permission->slug;
        return $this->getAllPermissions()->contains('slug', $permissionSlug);
    }

    public function users()
    {
        $userModel = config('dynamic-roles.user_model', \App\Models\User::class);
        return $this->belongsToMany(
            $userModel,
            config('dynamic-roles.table_names.user_role', 'user_role'),
            'role_id',
            'user_id'
        );
    }

    public function givePermissionTo(...$permissions)
    {
        $permissions = $this->getPermissionModels($permissions);
        if ($permissions->isEmpty()) return $this;
        $this->permissions()->syncWithoutDetaching($permissions);
        return $this;
    }

    public function revokePermissionTo(...$permissions)
    {
        $permissions = $this->getPermissionModels($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $permissions = $this->getPermissionModels($permissions);
        $this->permissions()->sync($permissions);
        return $this;
    }

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

