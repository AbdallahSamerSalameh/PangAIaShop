@extends('admin.layouts.app')

@section('title', 'Audit Log Details')
@section('header', 'Audit Log Details')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.audit-logs.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-1"></i> Back to Audit Logs
        </a>
    </div>

    <!-- Log Info Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">Audit Log Details</h2>
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
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
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Administrator</h3>
                    <div class="mt-2 flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" 
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
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Resource</h3>
                    <p class="mt-2 text-sm text-gray-900">
                        {{ ucfirst($log->resource) }}
                        @if($log->resource_id)
                            <span class="text-xs text-gray-400">#{{ $log->resource_id }}</span>
                        @endif
                    </p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">IP Address</h3>
                    <p class="mt-2 text-sm text-gray-900">{{ $log->ip_address }}</p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Date & Time</h3>
                    <p class="mt-2 text-sm text-gray-900">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                </div>
            </div>
            
            @if($log->user_agent)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-sm font-medium text-gray-500">User Agent</h3>
                    <p class="mt-2 text-sm text-gray-900 break-words">{{ $log->user_agent }}</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Data Changes -->
    @if($log->previous_data || $log->new_data)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="font-semibold text-xl text-gray-800">Data Changes</h2>
            </div>
            
            <div class="p-6">
                @if($log->previous_data && $log->new_data)
                    <div x-data="{ mode: 'side-by-side' }">
                        <!-- View Selector -->
                        <div class="mb-4 flex space-x-2">
                            <button @click="mode = 'side-by-side'" :class="{'bg-blue-600 text-white': mode === 'side-by-side', 'bg-gray-200 text-gray-800': mode !== 'side-by-side'}" 
                                class="px-3 py-2 rounded text-sm font-medium">
                                Side by Side
                            </button>
                            <button @click="mode = 'unified'" :class="{'bg-blue-600 text-white': mode === 'unified', 'bg-gray-200 text-gray-800': mode !== 'unified'}" 
                                class="px-3 py-2 rounded text-sm font-medium">
                                Unified View
                            </button>
                        </div>
                        
                        <!-- Side by Side View -->
                        <div x-show="mode === 'side-by-side'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Previous Data</h3>
                                <div class="bg-red-50 p-4 rounded-md overflow-x-auto">
                                    <pre class="text-xs text-red-800">{{ json_encode(json_decode($log->previous_data), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">New Data</h3>
                                <div class="bg-green-50 p-4 rounded-md overflow-x-auto">
                                    <pre class="text-xs text-green-800">{{ json_encode(json_decode($log->new_data), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Unified View -->
                        <div x-show="mode === 'unified'" class="space-y-4">
                            @php
                                $previousData = json_decode($log->previous_data, true) ?? [];
                                $newData = json_decode($log->new_data, true) ?? [];
                                $allKeys = array_unique(array_merge(array_keys($previousData), array_keys($newData)));
                                sort($allKeys);
                            @endphp
                            
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Value</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Value</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($allKeys as $key)
                                        @php
                                            $oldValue = $previousData[$key] ?? null;
                                            $newValue = $newData[$key] ?? null;
                                            $hasChanged = $oldValue !== $newValue;
                                        @endphp
                                        <tr class="{{ $hasChanged ? 'bg-yellow-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $key }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 {{ $hasChanged ? 'bg-red-50' : '' }}">
                                                @if(is_array($oldValue))
                                                    <pre class="text-xs">{{ json_encode($oldValue, JSON_PRETTY_PRINT) }}</pre>
                                                @elseif(is_bool($oldValue))
                                                    {{ $oldValue ? 'true' : 'false' }}
                                                @elseif(is_null($oldValue) && array_key_exists($key, $previousData))
                                                    <span class="text-gray-400">null</span>
                                                @elseif(!array_key_exists($key, $previousData))
                                                    <span class="text-gray-400">not set</span>
                                                @else
                                                    {{ $oldValue }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 {{ $hasChanged ? 'bg-green-50' : '' }}">
                                                @if(is_array($newValue))
                                                    <pre class="text-xs">{{ json_encode($newValue, JSON_PRETTY_PRINT) }}</pre>
                                                @elseif(is_bool($newValue))
                                                    {{ $newValue ? 'true' : 'false' }}
                                                @elseif(is_null($newValue) && array_key_exists($key, $newData))
                                                    <span class="text-gray-400">null</span>
                                                @elseif(!array_key_exists($key, $newData))
                                                    <span class="text-gray-400">not set</span>
                                                @else
                                                    {{ $newValue }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($log->previous_data)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Previous Data (Deleted)</h3>
                        <div class="bg-red-50 p-4 rounded-md overflow-x-auto">
                            <pre class="text-xs text-red-800">{{ json_encode(json_decode($log->previous_data), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @elseif($log->new_data)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">New Data (Created)</h3>
                        <div class="bg-green-50 p-4 rounded-md overflow-x-auto">
                            <pre class="text-xs text-green-800">{{ json_encode(json_decode($log->new_data), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Any additional JS needed for the view
    });
</script>
@endsection