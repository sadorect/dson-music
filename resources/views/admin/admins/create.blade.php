@extends('layouts.admin')

@section('title', 'Create Admin User')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.admins.index') }}" class="text-blue-500 hover:text-blue-700 mr-2">
            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Admin Users
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-semibold mb-6">Create Admin User</h1>

            <form action="{{ route('admin.admins.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" id="password" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                @if(auth()->user()->isSuperAdmin() && ($user->user_type ?? old('user_type')) == 'admin')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Admin Type</label>
                    <div class="mt-2">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="is_super_admin" name="is_super_admin" value="1" 
                                   {{ old('is_super_admin') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   onchange="togglePermissions(this.checked)">
                            <label for="is_super_admin" class="ml-2 block text-sm text-gray-900">
                                Super Admin (all permissions)
                            </label>
                        </div>
                    </div>
                    @error('is_super_admin')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="permissions_section" class="mb-6 p-4 bg-gray-50 rounded-lg {{ old('is_super_admin') ? 'hidden' : '' }}">
                    <h3 class="font-medium text-gray-700 mb-3">Permissions</h3>
                    <p class="text-sm text-gray-500 mb-4">Select the permissions for this delegated admin:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($permissions as $key => $label)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="permission_{{ $key }}" name="permissions[]" value="{{ $key }}"
                                           {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="permission_{{ $key }}" class="font-medium text-gray-700">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Create Admin User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePermissions(isSuperAdmin) {
        const permissionsSection = document.getElementById('permissions_section');
        if (isSuperAdmin) {
            permissionsSection.classList.add('hidden');
        } else {
            permissionsSection.classList.remove('hidden');
        }
    }
</script>
@endsection
