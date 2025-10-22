@extends('dynamic-roles::layout')

@section('title', 'Users')
@section('header', 'Users & Permissions Management')

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">All Users</h3>
    </div>
    <div class="border-t border-gray-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Direct Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                            @forelse($user->roles as $role)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-400">No roles</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                         <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ $user->directPermissions->count() }} direct
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                        <button onclick="openRoleModal({{ $user->id }}, '{{ $user->name }}', {{ $user->roles->pluck('id') }})" 
                                class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-user-shield"></i> Manage Roles
                        </button>
                        <button onclick="openPermissionModal({{ $user->id }}, '{{ $user->name }}', {{ $user->directPermissions->pluck('id') }})" 
                                class="text-green-600 hover:text-green-900">
                            <i class="fas fa-key"></i> Manage Permissions
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 bg-gray-50 border-t">
        {{ $users->links() }}
    </div>
</div>

<!-- Role Assignment Modal -->
<div id="roleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Manage Roles for <span id="roleUserName" class="font-semibold"></span></h3>
            <form id="roleForm" method="POST" action="{{ route('dynamic-roles.users.sync-roles') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="roleUserId">
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($roles as $role)
                    <div class="flex items-center">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}" class="role-checkbox h-4 w-4">
                        <label for="role-{{ $role->id }}" class="ml-2 block text-sm text-gray-900">{{ $role->name }}</label>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('roleModal')" class="px-4 py-2 bg-gray-300 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Update Roles</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permission Assignment Modal -->
<div id="permissionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Manage Direct Permissions for <span id="permissionUserName" class="font-semibold"></span></h3>
            <form id="permissionForm" method="POST" action="{{ route('dynamic-roles.users.sync-permissions') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="permissionUserId">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach($permissions as $group => $groupPermissions)
                    <div class="mb-4">
                        <h4 class="text-md font-semibold text-gray-700 mb-2 bg-gray-100 px-3 py-2 rounded">{{ $group }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 px-3">
                            @foreach($groupPermissions as $permission)
                            <div class="flex items-center">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->id }}" class="permission-checkbox h-4 w-4">
                                <label for="permission-{{ $permission->id }}" class="ml-2 block text-sm text-gray-700">{{ $permission->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('permissionModal')" class="px-4 py-2 bg-gray-300 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Update Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRoleModal(userId, userName, userRoles) {
    document.getElementById('roleModal').classList.remove('hidden');
    document.getElementById('roleUserId').value = userId;
    document.getElementById('roleUserName').textContent = userName;
    document.querySelectorAll('.role-checkbox').forEach(c => c.checked = false);
    userRoles.forEach(roleId => {
        const checkbox = document.getElementById('role-' + roleId);
        if (checkbox) checkbox.checked = true;
    });
}

function openPermissionModal(userId, userName, userPermissions) {
    document.getElementById('permissionModal').classList.remove('hidden');
    document.getElementById('permissionUserId').value = userId;
    document.getElementById('permissionUserName').textContent = userName;
    document.querySelectorAll('.permission-checkbox').forEach(c => c.checked = false);
    userPermissions.forEach(permissionId => {
        const checkbox = document.getElementById('permission-' + permissionId);
        if (checkbox) checkbox.checked = true;
    });
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

window.onclick = function(event) {
    if (event.target.id === 'roleModal' || event.target.id === 'permissionModal') {
        closeModal(event.target.id);
    }
}
</script>
@endsection
