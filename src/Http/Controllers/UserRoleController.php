<?php

namespace sndpbag\DynamicRoles\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use sndpbag\DynamicRoles\Models\Role;

class UserRoleController extends Controller
{
    public function index()
    {
        $userModel = config('dynamic-roles.user_model', \App\Models\User::class);
        $users = $userModel::with('roles')->paginate(15);
        $roles = Role::all();
        
        return view('dynamic-roles::users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:' . config('dynamic-roles.table_names.roles', 'roles') . ',id',
        ]);

        $userModel = config('dynamic-roles.user_model', \App\Models\User::class);
        $user = $userModel::findOrFail($request->user_id);
        
        $user->assignRole($request->role_id);

        return back()->with('success', 'Role assigned successfully');
    }

    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:' . config('dynamic-roles.table_names.roles', 'roles') . ',id',
        ]);

        $userModel = config('dynamic-roles.user_model', \App\Models\User::class);
        $user = $userModel::findOrFail($request->user_id);
        
        $user->removeRole($request->role_id);

        return back()->with('success', 'Role removed successfully');
    }

    public function syncRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'array',
            'roles.*' => 'exists:' . config('dynamic-roles.table_names.roles', 'roles') . ',id',
        ]);

        $userModel = config('dynamic-roles.user_model', \App\Models\User::class);
        $user = $userModel::findOrFail($request->user_id);
        
        $user->syncRoles($request->roles ?? []);

        return back()->with('success', 'User roles updated successfully');
    }
}