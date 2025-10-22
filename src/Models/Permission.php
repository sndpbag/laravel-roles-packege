<?php

namespace Sndpbag\DynamicRoles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'group',
        'route_name',
        'http_method',
        'http_uri',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('dynamic-roles.table_names.permissions', 'permissions'));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = Str::slug($permission->name);
            }
        });
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            config('dynamic-roles.table_names.role_permission', 'role_permission'),
            'permission_id',
            'role_id'
        );
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}