@extends('dynamic-roles::layout')

@section('title', 'Users')
@section('header', 'Users Management')

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">All Users</h3>
    </div>
    <div class="border-t border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="openRoleModal({{ $user->id }}, '{{ $user->name }}', {{ $user->roles->pluck('id') }})" 
                                class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-user-cog"></i> Manage Roles
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No users found</td>
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
            <h3 class="text-lg font-medium text-gray-900 mb-4">Manage User Roles</h3>
            <p class="text-sm text-gray-600 mb-4">User: <span id="userName" class="font-semibold"></span></p>
            
            <form id="roleForm" method="POST" action="{{ route('dynamic-roles.users.sync-roles') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="userId">
                
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($roles as $role)
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="roles[]" 
                               value="{{ $role->id }}" 
                               id="role-{{ $role->id }}"
                               class="role-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="role-{{ $role->id }}" class="ml-2 block text-sm text-gray-900">
                            {{ $role->name }}
                        </label>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeRoleModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Roles
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRoleModal(userId, userName, userRoles) {
    document.getElementById('roleModal').classList.remove('hidden');
    document.getElementById('userId').value = userId;
    document.getElementById('userName').textContent = userName;
    
    // Uncheck all checkboxes first
    document.querySelectorAll('.role-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Check user's current roles
    userRoles.forEach(roleId => {
        const checkbox = document.getElementById('role-' + roleId);
        if (checkbox) checkbox.checked = true;
    });
}

function closeRoleModal() {
    document.getElementById('roleModal').classList.add('hidden');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('roleModal');
    if (event.target == modal) {
        closeRoleModal();
    }
}
</script>
@endsection