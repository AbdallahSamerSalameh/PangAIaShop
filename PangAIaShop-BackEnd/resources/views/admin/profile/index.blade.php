@extends('admin.layouts.app')

@section('title', 'My Profile')
@section('header', 'My Profile')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Profile Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 text-center">
                <div class="mb-4">
                    <img class="h-32 w-32 rounded-full mx-auto" 
                        src="{{ $admin->avatar_url ? Storage::url($admin->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($admin->username) . '&color=7F9CF5&background=EBF4FF&size=128' }}" 
                        alt="{{ $admin->username }}">
                </div>
                <h3 class="text-xl font-semibold">{{ $admin->username }}</h3>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $admin->role === 'Super Admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $admin->role }}
                </span>
                <p class="mt-2 text-gray-600">{{ $admin->email }}</p>
                @if($admin->phone_number)
                    <p class="text-gray-600">{{ $admin->phone_number }}</p>
                @endif
            </div>
            
            <div class="border-t px-6 py-4">
                <h4 class="font-medium text-gray-800 mb-2">Account Information</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Admin ID</p>
                        <p>{{ $admin->id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status</p>
                        <p class="{{ $admin->is_active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Created</p>
                        <p>{{ $admin->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Last Login</p>
                        <p>{{ $admin->last_login ? $admin->last_login->format('M d, Y') : 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Edit Profile Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="font-semibold text-lg">Edit Profile</h3>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
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
                        
                        <!-- Phone Number -->
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $admin->phone_number) }}"
                                class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                            @error('phone_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Avatar -->
                        <div>
                            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                            <input type="file" name="avatar" id="avatar" 
                                class="border border-gray-300 rounded-md shadow-sm w-full py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, or GIF up to 2MB</p>
                            @error('avatar')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t">
                        <h4 class="font-medium text-gray-800 mb-3">Change Password</h4>
                        <p class="text-sm text-gray-600 mb-4">Leave these fields empty if you don't want to change your password.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" id="current_password"
                                    class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="password" id="password"
                                    class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection