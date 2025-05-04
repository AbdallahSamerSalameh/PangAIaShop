@extends('admin.layouts.app')

@section('title', 'Inventory Report')
@section('header', 'Inventory Report')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                        <i class="fas fa-box text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($totalProducts) }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                        <i class="fas fa-cubes text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Stock</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($totalStock) }} units</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-red-500 rounded-md">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Out of Stock</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($outOfStockCount) }} products</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                        <i class="fas fa-dollar-sign text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Inventory Value</h3>
                        <p class="text-lg font-semibold text-gray-900">${{ number_format($inventoryValue, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Inventory Report</h2>
        </div>
        
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ route('admin.reports.inventory') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                           placeholder="Product name or SKU">
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" id="category_id" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="stock_level" class="block text-sm font-medium text-gray-700">Stock Level</label>
                    <select name="stock_level" id="stock_level" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <option value="">All Stock Levels</option>
                        <option value="out_of_stock" {{ request('stock_level') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="low_stock" {{ request('stock_level') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="in_stock" {{ request('stock_level') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    
                    @if(request()->anyFilled(['search', 'category_id', 'stock_level', 'sort_field', 'sort_direction']))
                    <a href="{{ route('admin.reports.inventory') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.reports.inventory', array_merge(request()->query(), ['sort_field' => 'name', 'sort_direction' => request('sort_field') == 'name' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="group inline-flex items-center">
                                Product
                                @if(request('sort_field') == 'name')
                                    <i class="ml-1 fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                @else
                                    <i class="ml-1 fas fa-sort text-gray-400 opacity-0 group-hover:opacity-100"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.reports.inventory', array_merge(request()->query(), ['sort_field' => 'sku', 'sort_direction' => request('sort_field') == 'sku' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="group inline-flex items-center">
                                SKU
                                @if(request('sort_field') == 'sku')
                                    <i class="ml-1 fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                @else
                                    <i class="ml-1 fas fa-sort text-gray-400 opacity-0 group-hover:opacity-100"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.reports.inventory', array_merge(request()->query(), ['sort_field' => 'stock_quantity', 'sort_direction' => request('sort_field') == 'stock_quantity' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="group inline-flex items-center">
                                Stock
                                @if(request('sort_field') == 'stock_quantity' || !request('sort_field'))
                                    <i class="ml-1 fas fa-sort-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                @else
                                    <i class="ml-1 fas fa-sort text-gray-400 opacity-0 group-hover:opacity-100"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reorder Level
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.reports.inventory', array_merge(request()->query(), ['sort_field' => 'price', 'sort_direction' => request('sort_field') == 'price' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="group inline-flex items-center">
                                Price
                                @if(request('sort_field') == 'price')
                                    <i class="ml-1 fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                @else
                                    <i class="ml-1 fas fa-sort text-gray-400 opacity-0 group-hover:opacity-100"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Value
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($product->thumbnail)
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-md object-cover" src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-md flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="hover:text-blue-600">
                                                {{ $product->name }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->sku }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($product->categories->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($product->categories->take(2) as $category)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                        @if($product->categories->count() > 2)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                +{{ $product->categories->count() - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">No category</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->inventory)
                                    @php
                                        $stockLevel = $product->inventory->stock_quantity;
                                        $reorderLevel = $product->inventory->reorder_level;
                                    @endphp
                                    
                                    @if($stockLevel == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @elseif($stockLevel <= $reorderLevel)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ $stockLevel }} (Low)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $stockLevel }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->inventory ? $product->inventory->reorder_level : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($product->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($product->inventory)
                                    ${{ number_format($product->inventory->stock_quantity * $product->price, 2) }}
                                @else
                                    $0.00
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($product->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @elseif($product->status === 'inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No products found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-200">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
    
    <!-- Stock Level Summary -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Stock Level Summary</h2>
        </div>
        
        <div class="p-4">
            <div class="flex items-center justify-center">
                <div class="w-full max-w-md">
                    <div class="bg-gray-100 rounded-full h-4 mb-4">
                        @php
                            $outOfStockPercentage = $totalProducts > 0 ? ($outOfStockCount / $totalProducts) * 100 : 0;
                            $lowStockPercentage = $totalProducts > 0 ? ($lowStockCount / $totalProducts) * 100 : 0;
                            $inStockPercentage = 100 - $outOfStockPercentage - $lowStockPercentage;
                        @endphp
                        
                        <div class="flex h-4 rounded-full overflow-hidden">
                            <div class="bg-red-500 h-4" style="width: {{ $outOfStockPercentage }}%"></div>
                            <div class="bg-yellow-500 h-4" style="width: {{ $lowStockPercentage }}%"></div>
                            <div class="bg-green-500 h-4" style="width: {{ $inStockPercentage }}%"></div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block mr-1 bg-red-500 rounded-full"></span>
                            <span>Out of Stock ({{ $outOfStockCount }} products)</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block mr-1 bg-yellow-500 rounded-full"></span>
                            <span>Low Stock ({{ $lowStockCount }} products)</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block mr-1 bg-green-500 rounded-full"></span>
                            <span>In Stock ({{ $totalProducts - $outOfStockCount - $lowStockCount }} products)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection