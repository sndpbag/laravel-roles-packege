@extends('dynamic-roles::layout')

@section('title', 'Edit Role')
@section('header', 'Edit Role: ' . $role->name)

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
    <form action="{{ route('dynamic-roles.roles.update', $role) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $role->name) }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       required>
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       value="{{ old('slug', $role->slug) }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description', $role->description) }}</textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1"
                       {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Permissions</h3>
                
                @foreach($permissions as $group => $groupPermissions)
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3 bg-gray-100 px-4 py-2 rounded">{{ $group }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 px-4">
                        @foreach($groupPermissions as $permission)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="permissions[]" 
                                   id="permission-{{ $permission->id }}" 
                                   value="{{ $permission->id }}"
                                   {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="permission-{{ $permission->id }}" class="ml-2 block text-sm text-gray-700">
                                {{ $permission->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('dynamic-roles.roles.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Update Role
            </button>
        </div>
    </form>
</div>
@endsection