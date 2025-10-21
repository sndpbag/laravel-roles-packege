<?php

namespace sndpbag\DynamicRoles\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use sndpbag\DynamicRoles\Models\Role;

class UserRoleController extends Controller
{
    /**
     * The User model class name.
     *
     * @var string
     */
    protected $userModel;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Load the User model from the config only once
        $this->userModel = config('dynamic-roles.user_model', \App\Models\User::class);
    }

    public function index()
    {
        $users = $this->userModel::with('roles')->paginate(15);
        $roles = Role::all();
        
        return view('dynamic-roles::users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request)
    {
        // Get the users table name directly from the User model
        $userTable = (new $this->userModel)->getTable();

        $request->validate([
            'user_id' => "required|exists:{$userTable},id",
            'role_id' => 'required|exists:' . config('dynamic-roles.table_names.roles', 'roles') . ',id',
        ]);

        $user = $this->userModel::findOrFail($request->user_id);
        
        $user->assignRole($request->role_id);

        return back()->with('success', 'Role assigned successfully');
    }

    public function removeRole(Request $request)
    {
        $userTable = (new $this->userModel)->getTable();

        $request->validate([
            'user_id' => "required|exists:{$userTable},id",
            'role_id' => 'required|exists:' . config('dynamic-roles.table_names.roles', 'roles') . ',id',
        ]);

        $user = $this->userModel::findOrFail($request->user_id);
        
        $user->removeRole($request->role_id);

        return back()->with('success', 'Role removed successfully');
    }

    public function syncRoles(Request $request)
    {
        $userTable = (new $this->userModel)->getTable();

        $request->validate([
            'user_id' => "required|exists:{$userTable},id",
            'roles' => 'array',
            'roles.*' => 'exists:' . config('dynamic-roles.table_names.roles', 'roles') . ',id',
        ]);

        $user = $this->userModel::findOrFail($request->user_id);
        
        $user->syncRoles($request->roles ?? []);

        return back()->with('success', 'User roles updated successfully');
    }
}
