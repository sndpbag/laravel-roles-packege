@extends('dynamic-roles::layout')

@section('title', 'Create Role')
@section('header', 'Create New Role')

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
    <form action="{{ route('dynamic-roles.roles.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       required>
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug (Optional)</label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       value="{{ old('slug') }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="Auto-generated if left empty">
            </div>

            <div class="md:col-span-2">
                <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Role (Optional)</label>
                <select name="parent_id" 
                        id="parent_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">No Parent</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('parent_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-2 flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('dynamic-roles.roles.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Create Role
            </button>
        </div>
    </form>
</div>
@endsection
