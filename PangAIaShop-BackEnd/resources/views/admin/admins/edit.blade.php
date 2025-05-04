@extends('admin.layouts.app')

@section('title', 'Edit Administrator')
@section('header', 'Edit Administrator')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.admins.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-1"></i> Back to Administrators
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Edit Administrator</h2>
                
                <div class="flex items-center">
                    <span class="mr-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $admin->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $admin->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $admin->role === 'Super Admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $admin->role }}
                    </span>
                </div>
            </div>

            <form action="{{ route('admin.admins.update', $admin) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-600">*</span></label>
                        <input type="text" name="username" id="username" value="{{ old('username', $admin->username) }}" required
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password (optional for updates) -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password 
                            <span class="text-gray-500 font-normal">(leave blank to keep current)</span>
                        </label>
                        <input type="password" name="password" id="password"
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-600">*</span></label>
                        <select name="role" id="role" required
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                            <option value="admin" {{ (old('role', $admin->role) === 'admin') ? 'selected' : '' }}>Admin</option>
                            <option value="Super Admin" {{ (old('role', $admin->role) === 'Super Admin') ? 'selected' : '' }}>Super Admin</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $admin->phone_number) }}"
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="mt-2">
                            <div class="flex items-center">
                                <input id="is_active_yes" name="is_active" type="radio" value="1" 
                                    {{ (old('is_active', $admin->is_active) == 1) ? 'checked' : '' }} 
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="is_active_yes" class="ml-2 block text-sm text-gray-700">Active</label>
                            </div>
                            <div class="flex items-center mt-2">
                                <input id="is_active_no" name="is_active" type="radio" value="0" 
                                    {{ (old('is_active', $admin->is_active) == 0) ? 'checked' : '' }} 
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="is_active_no" class="ml-2 block text-sm text-gray-700">Inactive</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t pt-4 flex justify-end">
                    <a href="{{ route('admin.admins.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Administrator
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Panel -->
    <div class="bg-white rounded-lg shadow-md mt-6 p-6">
        <h3 class="text-lg font-semibold mb-3">Administrator Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Created By</p>
                <p>{{ $admin->creator ? $admin->creator->username : 'System' }}</p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Created At</p>
                <p>{{ $admin->created_at->format('M d, Y h:i A') }}</p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Last Login</p>
                <p>{{ $admin->last_login ? $admin->last_login->format('M d, Y h:i A') : 'Never' }}</p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Last Password Change</p>
                <p>{{ $admin->last_password_change ? $admin->last_password_change->format('M d, Y h:i A') : 'Never' }}</p>
            </div>
        </div>
    </div>
@endsection