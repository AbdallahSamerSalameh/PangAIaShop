@extends('admin.layouts.app')

@section('title', 'Administrator Details')
@section('header', 'Administrator Details')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.admins.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-1"></i> Back to Administrators
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Admin Info -->
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
                            <p class="text-gray-500">Created By</p>
                            <p>{{ $admin->creator ? $admin->creator->username : 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Created At</p>
                            <p>{{ $admin->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Last Login</p>
                            <p>{{ $admin->last_login ? $admin->last_login->format('M d, Y H:i') : 'Never' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Failed Logins</p>
                            <p>{{ $admin->failed_login_count }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t px-6 py-4 flex justify-between">
                    <a href="{{ route('admin.admins.edit', $admin) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    
                    @if(Auth::guard('admin')->id() !== $admin->id)
                        <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this admin?')" 
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Right Column - Activities -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h3 class="font-semibold text-lg">Recent Activities</h3>
                </div>
                
                <div class="p-6">
                    @if($activityLogs->count() > 0)
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($activityLogs as $log)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex items-start space-x-3">
                                                <div class="relative">
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center ring-8 ring-white">
                                                        @switch($log->action)
                                                            @case('login')
                                                                <i class="fas fa-sign-in-alt text-green-500"></i>
                                                                @break
                                                            @case('logout')
                                                                <i class="fas fa-sign-out-alt text-red-500"></i>
                                                                @break
                                                            @case('login_failed')
                                                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                                                                @break
                                                            @case('admin_create')
                                                                <i class="fas fa-user-plus text-blue-500"></i>
                                                                @break
                                                            @case('admin_update')
                                                                <i class="fas fa-user-edit text-yellow-500"></i>
                                                                @break
                                                            @case('admin_delete')
                                                                <i class="fas fa-user-times text-red-500"></i>
                                                                @break
                                                            @case('profile_update')
                                                                <i class="fas fa-id-card text-purple-500"></i>
                                                                @break
                                                            @default
                                                                <i class="fas fa-history text-gray-500"></i>
                                                        @endswitch
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <div class="text-sm">
                                                            <span class="font-medium text-gray-900">
                                                                @switch($log->action)
                                                                    @case('login')
                                                                        Logged in
                                                                        @break
                                                                    @case('logout')
                                                                        Logged out
                                                                        @break
                                                                    @case('login_failed')
                                                                        Failed login attempt
                                                                        @break
                                                                    @case('admin_create')
                                                                        Created a new admin
                                                                        @break
                                                                    @case('admin_update')
                                                                        Updated admin information
                                                                        @break
                                                                    @case('admin_delete')
                                                                        Deleted an admin
                                                                        @break
                                                                    @case('profile_update')
                                                                        Updated profile
                                                                        @break
                                                                    @default
                                                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                                @endswitch
                                                            </span>
                                                        </div>
                                                        <p class="mt-0.5 text-sm text-gray-500">
                                                            {{ $log->created_at->format('M d, Y H:i:s') }}
                                                        </p>
                                                    </div>
                                                    <div class="mt-2 text-sm text-gray-700">
                                                        <p>
                                                            {{ $log->ip_address }} 
                                                            @if($log->resource_id && $log->resource_id != $admin->id)
                                                                <span class="text-xs">
                                                                    (ID: {{ $log->resource_id }})
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">View All Activity</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500">No activity logs found for this administrator.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h3 class="font-semibold text-lg">Administrator Statistics</h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-blue-800 font-medium">Products Created</h4>
                            <p class="text-2xl font-bold text-blue-600">{{ $admin->createdProducts()->count() }}</p>
                        </div>
                        
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-green-800 font-medium">Orders Processed</h4>
                            <p class="text-2xl font-bold text-green-600">{{ $admin->handledOrders()->count() }}</p>
                        </div>
                        
                        <div class="bg-yellow-50 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-yellow-800 font-medium">Categories Created</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $admin->categories()->count() }}</p>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-purple-800 font-medium">Tickets Handled</h4>
                            <p class="text-2xl font-bold text-purple-600">{{ $admin->assignedSupportTickets()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection