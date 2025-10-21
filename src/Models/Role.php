<?php

namespace sndpbag\DynamicRoles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
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

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('dynamic-roles.table_names.role_permission', 'role_permission'),
            'role_id',
            'permission_id'
        );
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

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }

        return $this->permissions->contains($permission);
    }

    public function givePermissionTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if ($permissions->isEmpty()) {
            return $this;
        }

        $this->permissions()->syncWithoutDetaching($permissions);
        return $this;
    }

    public function revokePermissionTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->sync($permissions);
        return $this;
    }

    protected function getAllPermissions($permissions)
    {
        return collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if ($permission instanceof Permission) {
                    return $permission;
                }
                return Permission::where('slug', $permission)->orWhere('id', $permission)->first();
            })
            ->filter()
            ->unique('id');
    }
}