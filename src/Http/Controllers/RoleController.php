<?php

namespace Sndpbag\DynamicRoles\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sndpbag\DynamicRoles\Models\Role;
use Sndpbag\DynamicRoles\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
         $roles = Role::with('permissions', 'parent')->paginate(15);
        return view('dynamic-roles::roles.index', compact('roles'));
    }

    public function create()
    {
        $roles = Role::all(); // Parent dropdown-er jonno shob role pathan
        return view('dynamic-roles::roles.create', compact('roles'));
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255|unique:roles,name',
    //         'slug' => 'nullable|string|max:255|unique:roles,slug',
    //         'description' => 'nullable|string',
    //         'is_active' => 'boolean',
    //     ]);

    //     $role = Role::create($validated);

    //     return redirect()->route('dynamic-roles.roles.index')
    //         ->with('success', 'Role created successfully');
    // }

     public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:'.config('dynamic-roles.table_names.roles', 'roles').',name',
            'slug' => 'nullable|string|max:255|unique:'.config('dynamic-roles.table_names.roles', 'roles').',slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:'.config('dynamic-roles.table_names.roles', 'roles').',id', // parent_id validate korun
        ]);

        $role = Role::create($validated);

        return redirect()->route('dynamic-roles.roles.index')
            ->with('success', 'Role created successfully');
    }



    // public function edit(Role $role)
    // {
    //     $permissions = Permission::all()->groupBy('group');
    //     $rolePermissions = $role->permissions->pluck('id')->toArray();
        
    //     return view('dynamic-roles::roles.edit', compact('role', 'permissions', 'rolePermissions'));
    // }

     public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        // Nijer role-ke parent list theke bad din
        $roles = Role::where('id', '!=', $role->id)->get(); 
        
        return view('dynamic-roles::roles.edit', compact('role', 'permissions', 'rolePermissions', 'roles'));
    }



    // public function update(Request $request, Role $role)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
    //         'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
    //         'description' => 'nullable|string',
    //         'is_active' => 'boolean',
    //     ]);

    //     $role->update($validated);

    //     if ($request->has('permissions')) {
    //         $role->syncPermissions($request->permissions);
    //     }

    //     return redirect()->route('dynamic-roles.roles.index')
    //         ->with('success', 'Role updated successfully');
    // }

      public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:'.config('dynamic-roles.table_names.roles', 'roles').',name,' . $role->id,
            'slug' => 'nullable|string|max:255|unique:'.config('dynamic-roles.table_names.roles', 'roles').',slug,' . $role->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:'.config('dynamic-roles.table_names.roles', 'roles').',id', // parent_id validate korun
        ]);

        $role->update($validated);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]); // Kono permission select na thakle shob remove korun
        }

    

    User::all()->each->clearPermissionsCache();

        return redirect()->route('dynamic-roles.roles.index')
            ->with('success', 'Role updated successfully');
    }


    public function destroy(Role $role)
    {
        if ($role->slug === config('dynamic-roles.super_admin_role')) {
            return back()->with('error', 'Cannot delete super admin role');
        }

        $role->delete();

        return redirect()->route('dynamic-roles.roles.index')
            ->with('success', 'Role deleted successfully');
    }

    public function permissions(Role $role)
    {
        $permissions = Permission::all()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('dynamic-roles::roles.permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return redirect()->route('dynamic-roles.roles.edit', $role)
            ->with('success', 'Permissions updated successfully');
    }
}