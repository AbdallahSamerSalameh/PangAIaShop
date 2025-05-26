<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Inventory;
use App\Models\ProductCategory;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Product::query();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'in_stock') {
                $query->where('in_stock', true);
            } elseif ($request->status === 'out_of_stock') {
                $query->where('in_stock', false);
            }
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'id');
        $orderDir = $request->get('order_dir', 'asc');
        
        // Validate order by field
        $validOrderByFields = ['id', 'name', 'price', 'created_at'];
        if (!in_array($orderBy, $validOrderByFields)) {
            $orderBy = 'id';
        }
        
        // Validate order direction
        $validOrderDirs = ['asc', 'desc'];
        if (!in_array($orderDir, $validOrderDirs)) {
            $orderDir = 'desc';
        }        $query->orderBy($orderBy, $orderDir);
        
        // Get per_page from request or use default
        $perPage = $request->input('per_page', 25);
        
        // Validate per_page to only allow specific values
        $validPerPage = [10, 25, 50, 100];
        if (!in_array((int)$perPage, $validPerPage)) {
            $perPage = 25;
        }
          // Paginate results with per_page from request
        $products = $query->with([
            'categories.children',
            'inventory', 
            'images' => function($q) {
                $q->orderBy('is_primary', 'desc'); // Get primary images first
            }
        ])->paginate($perPage);
          // For each product, find its most specific categories (leaf categories)
        foreach ($products as $product) {
            // Add a new property to hold the direct parent (most specific) category
            $leafCategories = $product->categories->filter(function($category) {
                return $category->isLeafCategory();
            });
            
            // Sort leaf categories by primary status first, then by name
            $product->directCategories = $leafCategories->sortByDesc(function($category) {
                return $category->pivot->is_primary_category;
            });
        }
        
        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get();
          // Log the action
        AdminAuditLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'view_list',
            'resource' => 'products',
            'resource_id' => 0, // Using 0 as default for list views where no specific resource ID is applicable
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products',
            'in_stock' => 'boolean',
            'is_featured' => 'boolean',
            'brand' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'main_image' => 'nullable|integer'
        ]);

        DB::beginTransaction();

        try {
            // Create product
            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'] ?? null,
                'sku' => $validated['sku'],
                'in_stock' => $request->boolean('in_stock'),
                'is_featured' => $request->boolean('is_featured'),
                'brand' => $validated['brand'] ?? null,
                'slug' => Str::slug($validated['name'])
            ]);            // Create inventory record
            Inventory::create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'location' => $validated['location'] ?? 'Main Warehouse', // Default location
                'low_stock_threshold' => 10, // Default low stock threshold
                'updated_by' => auth('admin')->id()            ]);

            // Associate categories using relationship to avoid trigger conflicts
            foreach ($validated['categories'] as $index => $categoryId) {
                // Insert without triggering model events that could conflict with database triggers
                DB::table('product_categories')->insert([
                    'product_id' => $product->id,
                    'category_id' => $categoryId,
                    'is_primary_category' => $index === 0 ? 1 : 0, // First category is primary
                    'added_by' => auth('admin')->id(),
                    'added_at' => now()
                ]);
            }// Handle images (both uploaded files and URLs)
            $imageIndex = 0;
            $mainImageIndex = $request->input('main_image', 0);
            
            // Process uploaded files
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('product-images', 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $path,
                        'is_primary' => ($imageIndex == $mainImageIndex) ? true : false,
                        'alt_text' => $validated['name'],
                        'display_order' => $imageIndex
                    ]);
                    $imageIndex++;
                }
            }
            
            // Process image URLs
            if ($request->has('image_urls')) {
                foreach ($request->input('image_urls') as $url) {
                    if (!empty($url)) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $url,
                            'is_primary' => ($imageIndex == $mainImageIndex) ? true : false,
                            'alt_text' => $validated['name'],
                            'display_order' => $imageIndex
                        ]);
                        $imageIndex++;
                    }
                }
            }// Log the action
            AdminAuditLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'create',
                'resource' => 'products',
                'resource_id' => $product->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    public function show($id)
    {
        $product = Product::with([
            'categories', 
            'inventory', 
            'images' => function($q) {
                $q->orderBy('is_primary', 'desc'); // Get primary images first
            }
        ])->findOrFail($id);
        
        // Add directCategories property (same logic as in index method)
        $leafCategories = $product->categories->filter(function($category) {
            return $category->isLeafCategory();
        });
        
        // Sort leaf categories by primary status first, then by name
        $product->directCategories = $leafCategories->sortByDesc(function($category) {
            return $category->pivot->is_primary_category;
        });
          
        // Log the action
        AdminAuditLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'view',
            'resource' => 'products',
            'resource_id' => $product->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::with(['categories', 'inventory', 'images'])->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $productCategoryIds = $product->categories->pluck('id')->toArray();
        
        return view('admin.products.edit', compact('product', 'categories', 'productCategoryIds'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
          $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'in_stock' => 'boolean',
            'is_featured' => 'boolean',
            'brand' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'integer|exists:product_images,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'main_image' => 'nullable|string',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id'
        ]);

        DB::beginTransaction();

        try {
            // Update product
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'] ?? null,
                'sku' => $validated['sku'],
                'in_stock' => $request->boolean('in_stock'),
                'is_featured' => $request->boolean('is_featured'),
                'brand' => $validated['brand'] ?? null,
                'slug' => Str::slug($validated['name'])
            ]);            // Update inventory
            $inventory = Inventory::where('product_id', $product->id)->first();            if ($inventory) {
                $inventory->update([
                    'quantity' => $validated['quantity'],
                    'location' => $validated['location'] ?? $inventory->location,
                    'updated_by' => auth('admin')->id()
                ]);} else {
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'location' => $validated['location'] ?? 'Main Warehouse', // Default location
                    'low_stock_threshold' => 10, // Default low stock threshold
                    'updated_by' => auth('admin')->id()
                ]);
            }            // Update categories - Use DB::table to avoid soft delete issues
            DB::table('product_categories')->where('product_id', $product->id)->delete();
            
            foreach ($validated['categories'] as $index => $categoryId) {
                // Insert without triggering model events that could conflict with database triggers
                DB::table('product_categories')->insert([
                    'product_id' => $product->id,
                    'category_id' => $categoryId,
                    'is_primary_category' => $index === 0 ? 1 : 0, // First category is primary
                    'added_by' => auth('admin')->id(),
                    'added_at' => now()
                ]);
            }

            // Handle main image selection
            if ($request->has('main_image')) {
                $mainImageId = $request->main_image;
                if (strpos($mainImageId, 'new-') === 0) {
                    // It's a new image, we'll handle it when processing uploads
                    $mainImageIsNew = true;
                    $mainImageIndex = (int) str_replace('new-', '', $mainImageId);
                } else {
                    // It's an existing image                    // Reset all main images
                    ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
                    // Set the selected one as main
                    ProductImage::where('id', $mainImageId)->update(['is_primary' => true]);
                }
            }

            // Delete selected images
            if ($request->has('delete_images')) {
                $imagesToDelete = $request->delete_images;
                $images = ProductImage::where('product_id', $product->id)
                    ->whereIn('id', $imagesToDelete)
                    ->get();
                
                foreach ($images as $image) {
                    // Delete file from storage
                    if (Storage::disk('public')->exists($image->image_url)) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                    
                    // Delete record
                    $image->delete();
                }
            }            // Upload new images
            if ($request->hasFile('images')) {
                $existingImagesCount = ProductImage::where('product_id', $product->id)->count();
                
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('product-images', 'public');
                    
                    $isMain = false;                    if (isset($mainImageIsNew) && $mainImageIsNew && $mainImageIndex == $index) {
                        // This new image should be the main one
                        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
                        $isMain = true;
                    }
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $path,
                        'is_primary' => $isMain,
                        'alt_text' => $validated['name'],
                        'display_order' => $existingImagesCount + $index
                    ]);
                }
            }
            
            // Add images from URLs
            if ($request->has('image_urls')) {
                $existingImagesCount = ProductImage::where('product_id', $product->id)->count();
                $urlIndex = 0;
                
                foreach ($request->input('image_urls') as $url) {
                    if (!empty($url)) {
                        $isMain = false;
                        if (isset($mainImageIsNew) && $mainImageIsNew && $mainImageIndex == ($urlIndex + ($request->hasFile('images') ? count($request->file('images')) : 0))) {
                            // This URL image should be the main one
                            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
                            $isMain = true;
                        }
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $url,
                            'is_primary' => $isMain,
                            'alt_text' => $validated['name'],
                            'display_order' => $existingImagesCount + $urlIndex + ($request->hasFile('images') ? count($request->file('images')) : 0)
                        ]);
                        $urlIndex++;
                    }
                }
            }// Log the action
            AdminAuditLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'update',
                'resource' => 'products',
                'resource_id' => $product->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $productName = $product->name;

        try {
            // Delete all related images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
            
            // The product will be soft deleted, related records will be cascade soft deleted
            $product->delete();
              // Log the action
            AdminAuditLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'delete',
                'resource' => 'products',
                'resource_id' => $id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }
}
