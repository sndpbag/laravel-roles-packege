<?php

namespace Sndpbag\DynamicRoles\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sndpbag\DynamicRoles\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all()->groupBy('group');
        return view('dynamic-roles::permissions.index', compact('permissions'));
    }

    public function create()
    {
        $groups = config('dynamic-roles.permission_groups', []);
        return view('dynamic-roles::permissions.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $tableName = config('dynamic-roles.table_names.permissions', 'permissions');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'slug' => 'nullable|string|max:255|unique:permissions,slug',
            'slug' => "nullable|string|max:255|unique:{$tableName},slug",
            'group' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Permission::create($validated);

        return redirect()->route('dynamic-roles.permissions.index')
            ->with('success', 'Permission created successfully');
    }

    public function edit(Permission $permission)
    {
        $groups = config('dynamic-roles.permission_groups', []);
        return view('dynamic-roles::permissions.edit', compact('permission', 'groups'));
    }

    public function update(Request $request, Permission $permission)
    {
        $tableName = config('dynamic-roles.table_names.permissions', 'permissions');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'slug' => 'nullable|string|max:255|unique:permissions,slug,' . $permission->id,
            'slug' => "nullable|string|max:255|unique:{$tableName},slug," . $permission->id,
            'group' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $permission->update($validated);

        return redirect()->route('dynamic-roles.permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('dynamic-roles.permissions.index')
            ->with('success', 'Permission deleted successfully');
    }

    public function sync()
    {
        \Artisan::call('dynamic-roles:sync-routes');

        return redirect()->route('dynamic-roles.permissions.index')
            ->with('success', 'Routes synced successfully');
    }
}