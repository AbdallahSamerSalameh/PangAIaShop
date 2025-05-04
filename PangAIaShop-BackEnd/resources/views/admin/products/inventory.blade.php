@extends('admin.layouts.app')

@section('title', 'Inventory Management')
@section('header', 'Inventory Management')

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 flex flex-wrap justify-between items-center">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Product Inventory</h3>
        <div class="flex flex-wrap gap-2">
            <!-- Filter Form -->
            <form action="{{ route('admin.products.inventory') }}" method="GET" class="flex flex-wrap gap-2">
                <div class="flex items-center">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                
                <div class="flex items-center">
                    <select name="stock_status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Stock Status</option>
                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>Normal Stock</option>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <select name="category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-center">
                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
                
                @if(request()->anyFilled(['search', 'stock_status', 'category_id']))
                    <div class="flex items-center">
                        <a href="{{ route('admin.products.inventory') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-1"></i> Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="border-t border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low Threshold</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-md overflow-hidden bg-gray-200">
                                        @if($product->images->isNotEmpty())
                                            <img src="{{ Storage::url($product->images->first()->image_url) }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover">
                                        @else
                                            <div class="h-10 w-10 flex items-center justify-center text-gray-400">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="hover:text-blue-600">
                                                {{ $product->name }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $product->vendor->name ?? 'No vendor' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->sku }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->categories->pluck('name')->implode(', ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center">
                                    <span class="font-medium {{ $product->inventory->first() ? ($product->inventory->first()->quantity <= 0 ? 'text-red-600' : ($product->inventory->first()->quantity <= $product->inventory->first()->low_stock_threshold ? 'text-yellow-600' : 'text-gray-900')) : 'text-red-600' }}">
                                        {{ $product->inventory->first() ? $product->inventory->first()->quantity : 0 }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->inventory->first() ? $product->inventory->first()->low_stock_threshold : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $product->inventory->first() ? 
                                        ($product->inventory->first()->quantity <= 0 ? 'bg-red-100 text-red-800' : 
                                            ($product->inventory->first()->quantity <= $product->inventory->first()->low_stock_threshold ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) 
                                        : 'bg-red-100 text-red-800' }}">
                                    {{ $product->inventory->first() ? 
                                        ($product->inventory->first()->quantity <= 0 ? 'Out of Stock' : 
                                            ($product->inventory->first()->quantity <= $product->inventory->first()->low_stock_threshold ? 'Low Stock' : 'In Stock')) 
                                        : 'No Inventory' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="#" onclick="document.getElementById('update-stock-{{ $product->id }}').classList.toggle('hidden')" class="text-indigo-600 hover:text-indigo-900" title="Update Stock">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-900" title="Edit Product">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                </div>
                                <!-- Inline update form (hidden by default) -->
                                <div id="update-stock-{{ $product->id }}" class="hidden mt-2 bg-gray-50 p-2 rounded border">
                                    <form action="{{ route('admin.inventory.update-stock', $product->id) }}" method="POST" class="flex items-center space-x-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="quantity" value="{{ $product->inventory->first() ? $product->inventory->first()->quantity : 0 }}" min="0" 
                                            class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                            Save
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $products->appends(request()->query())->links() }}
    </div>
</div>
@endsection