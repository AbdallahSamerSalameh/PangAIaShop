@extends('admin.layouts.app')

@section('title', 'Audit Logs')
@section('header', 'Audit Logs')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Audit Logs</h1>
            <p class="text-gray-600">Track all administrator activities in the system</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-1">Administrator</label>
                    <select name="admin_id" id="admin_id" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Administrators</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->username }} ({{ $admin->role }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select name="action" id="action" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="resource" class="block text-sm font-medium text-gray-700 mb-1">Resource Type</label>
                    <select name="resource" id="resource" class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Resources</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource }}" {{ request('resource') == $resource ? 'selected' : '' }}>
                                {{ ucfirst($resource) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="From">
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                            class="border-gray-300 rounded-md shadow-sm w-full focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="To">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if(request()->anyFilled(['admin_id', 'action', 'resource', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-full" 
                                            src="{{ $log->admin->avatar_url ? Storage::url($log->admin->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($log->admin->username) . '&color=7F9CF5&background=EBF4FF' }}" 
                                            alt="{{ $log->admin->username }}">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->admin->username }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $log->admin->role }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if(in_array($log->action, ['login', 'admin_create', 'product_create', 'order_create'])) 
                                        bg-green-100 text-green-800
                                    @elseif(in_array($log->action, ['logout', 'admin_update', 'product_update', 'order_update'])) 
                                        bg-blue-100 text-blue-800
                                    @elseif(in_array($log->action, ['login_failed', 'admin_delete', 'product_delete', 'order_delete'])) 
                                        bg-red-100 text-red-800
                                    @else 
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ucfirst($log->resource) }}
                                @if($log->resource_id)
                                    <span class="text-xs text-gray-400">#{{ $log->resource_id }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No audit logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
@endsection