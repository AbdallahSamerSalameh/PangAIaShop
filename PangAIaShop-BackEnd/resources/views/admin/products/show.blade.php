@extends('admin.layouts.app')

@section('title', $product->name)
@section('header', 'Product Details')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
            <p class="text-gray-600">SKU: {{ $product->sku }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Are you sure you want to delete this product?')" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('admin.products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left column: Product Images -->
        <div class="md:col-span-1 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-700">Product Images</h3>
            </div>
            <div class="p-6">
                @if($product->images->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($product->images as $image)
                            <div class="relative group">
                                <img src="{{ Storage::url($image->image_url) }}" alt="{{ $image->alt_text }}" class="rounded-lg w-full h-40 object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <a href="{{ Storage::url($image->image_url) }}" target="_blank" class="text-white p-2 rounded-full bg-blue-500 hover:bg-blue-600 mr-2">
                                        <i class="fas fa-expand"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center p-10 border-2 border-dashed border-gray-300 rounded-lg">
                        <i class="fas fa-image text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500">No images available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Middle column: Product Information -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-700">Product Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-4">Basic Information</h4>
                        <dl class="space-y-3">
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Name:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">{{ $product->name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">SKU:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">{{ $product->sku }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Price:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">${{ number_format($product->price, 2) }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Status:</dt>
                                <dd class="text-sm w-2/3">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($product->status === 'active') bg-green-100 text-green-800 
                                        @elseif($product->status === 'inactive') bg-gray-100 text-gray-800 
                                        @elseif($product->status === 'out_of_stock') bg-red-100 text-red-800 
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Categories:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">
                                    @if($product->categories->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($product->categories as $category)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">No category</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Vendor:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">
                                    @if($product->vendor)
                                        {{ $product->vendor->name }}
                                    @else
                                        <span class="text-gray-400">No vendor</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Created At:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">{{ $product->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Updated At:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">{{ $product->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Stock & Details -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-4">Stock & Details</h4>
                        <dl class="space-y-3">
                            @if($product->inventory->isNotEmpty())
                                <div class="flex">
                                    <dt class="text-sm font-medium text-gray-500 w-1/3">Stock:</dt>
                                    <dd class="text-sm text-gray-900 w-2/3">
                                        {{ $product->inventory->first()->stock_quantity }}
                                        @if($product->inventory->first()->stock_quantity <= $product->inventory->first()->stock_threshold)
                                            <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Low Stock
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-sm font-medium text-gray-500 w-1/3">Threshold:</dt>
                                    <dd class="text-sm text-gray-900 w-2/3">{{ $product->inventory->first()->stock_threshold }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-sm font-medium text-gray-500 w-1/3">Last Updated:</dt>
                                    <dd class="text-sm text-gray-900 w-2/3">{{ $product->inventory->first()->updated_at->format('M d, Y H:i') }}</dd>
                                </div>
                            @else
                                <div class="flex">
                                    <dt class="text-sm font-medium text-gray-500 w-1/3">Stock:</dt>
                                    <dd class="text-sm text-gray-900 w-2/3">
                                        <span class="text-gray-400">No inventory information</span>
                                    </dd>
                                </div>
                            @endif
                            
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Weight:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">
                                    {{ $product->weight ? $product->weight . ' kg' : 'Not specified' }}
                                </dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-1/3">Dimensions:</dt>
                                <dd class="text-sm text-gray-900 w-2/3">
                                    {{ $product->dimensions ?: 'Not specified' }}
                                </dd>
                            </div>
                            
                            @if($product->variants->isNotEmpty())
                                <div class="flex">
                                    <dt class="text-sm font-medium text-gray-500 w-1/3">Variants:</dt>
                                    <dd class="text-sm text-gray-900 w-2/3">{{ $product->variants->count() }} variants</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <h4 class="font-medium text-gray-700 mb-2">Description</h4>
                    <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-800">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    @if($product->warranty_info)
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Warranty Information</h4>
                            <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-800">
                                {!! nl2br(e($product->warranty_info)) !!}
                            </div>
                        </div>
                    @endif

                    @if($product->return_policy)
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Return Policy</h4>
                            <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-800">
                                {!! nl2br(e($product->return_policy)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Variants, Reviews, Price History Tabs -->
    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200">
            <ul class="flex -mb-px" x-data="{ activeTab: 'variants' }">
                <li class="mr-2">
                    <a href="#" @click.prevent="activeTab = 'variants'" :class="activeTab === 'variants' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="inline-block py-4 px-4 border-b-2 font-medium text-sm">
                        <i class="fas fa-tags mr-1"></i> Variants ({{ $product->variants->count() }})
                    </a>
                </li>
                <li class="mr-2">
                    <a href="#" @click.prevent="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="inline-block py-4 px-4 border-b-2 font-medium text-sm">
                        <i class="fas fa-star mr-1"></i> Reviews ({{ $product->reviews->count() }})
                    </a>
                </li>
                <li class="mr-2">
                    <a href="#" @click.prevent="activeTab = 'price-history'" :class="activeTab === 'price-history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="inline-block py-4 px-4 border-b-2 font-medium text-sm">
                        <i class="fas fa-chart-line mr-1"></i> Price History
                    </a>
                </li>
            </ul>
        </div>

        <div class="p-6" x-data="{ activeTab: 'variants' }">
            <!-- Variants Tab -->
            <div x-show="activeTab === 'variants'">
                @if($product->variants->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Options</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->variants as $variant)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $variant->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $variant->sku }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($variant->price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $variant->stock_quantity ?? 'N/A' }}
                                            @if(isset($variant->stock_quantity) && $variant->stock_quantity <= ($variant->stock_threshold ?? 5))
                                                <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Low
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $variant->options }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No variants available for this product</p>
                    </div>
                @endif
            </div>

            <!-- Reviews Tab -->
            <div x-show="activeTab === 'reviews'" x-cloak>
                @if($product->reviews->isNotEmpty())
                    <div class="space-y-6">
                        @foreach($product->reviews as $review)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between mb-2">
                                    <div>
                                        <div class="flex items-center">
                                            <div class="text-yellow-400">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating)
                                                        <i class="fas fa-star"></i>
                                                    @else
                                                        <i class="far fa-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="ml-2 text-sm font-medium text-gray-900">{{ $review->title }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500">By {{ $review->user->name }} on {{ $review->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($review->status === 'approved') bg-green-100 text-green-800 
                                            @elseif($review->status === 'pending') bg-yellow-100 text-yellow-800 
                                            @elseif($review->status === 'rejected') bg-red-100 text-red-800 
                                            @endif">
                                            {{ ucfirst($review->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-700">
                                    {{ $review->content }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No reviews available for this product</p>
                    </div>
                @endif
            </div>

            <!-- Price History Tab -->
            <div x-show="activeTab === 'price-history'" x-cloak>
                @if($product->priceHistory->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changed By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->priceHistory->sortByDesc('created_at') as $priceRecord)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $priceRecord->created_at->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${{ number_format($priceRecord->price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($priceRecord->changedBy)
                                                {{ $priceRecord->changedBy->username }}
                                            @else
                                                System
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No price history available for this product</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
@endsection