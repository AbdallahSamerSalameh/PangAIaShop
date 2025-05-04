<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();
            
            // Apply search filter
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }
            
            // Apply category filter
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            // Apply status filter
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Apply sorting
            $sortField = $request->sort_field ?? 'created_at';
            $sortDirection = $request->sort_direction ?? 'desc';
            $query->orderBy($sortField, $sortDirection);
            
            // Include relationships
            $query->with(['category:id,name', 'inventory']);
            
            // Paginate results
            $perPage = $request->per_page ?? 10;
            $products = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|max:50|unique:products',
            'status' => 'required|in:active,inactive,out_of_stock',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'featured' => 'boolean',
            'stock' => 'required|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // Create product
            $product = new Product();
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category_id = $request->category_id;
            $product->sku = $request->sku;
            $product->status = $request->status;
            $product->weight = $request->weight;
            $product->dimensions = $request->dimensions;
            $product->featured = $request->featured ?? false;
            $product->created_by = $request->user()->id;
            $product->save();
            
            // Create inventory record
            $inventory = new Inventory();
            $inventory->product_id = $product->id;
            $inventory->stock = $request->stock;
            $inventory->updated_by = $request->user()->id;
            $inventory->save();
            
            // Create price history
            $priceHistory = new PriceHistory();
            $priceHistory->product_id = $product->id;
            $priceHistory->price = $request->price;
            $priceHistory->changed_by = $request->user()->id;
            $priceHistory->save();
            
            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'file_name' => $image->getClientOriginalName(),
                        'is_primary' => $product->images()->count() === 0
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'inventory', 'images'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $product = Product::with(['category', 'inventory', 'images'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Update the specified product.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'sku' => 'sometimes|required|string|max:50|unique:products,sku,' . $id,
            'status' => 'sometimes|required|in:active,inactive,out_of_stock',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'featured' => 'boolean',
            'stock' => 'sometimes|required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $product = Product::findOrFail($id);
            
            // Update product fields if provided
            if ($request->has('name')) {
                $product->name = $request->name;
                $product->slug = Str::slug($request->name);
            }
            
            if ($request->has('description')) {
                $product->description = $request->description;
            }
            
            if ($request->has('price') && $request->price != $product->price) {
                $oldPrice = $product->price;
                $product->price = $request->price;
                
                // Create price history record
                $priceHistory = new PriceHistory();
                $priceHistory->product_id = $product->id;
                $priceHistory->old_price = $oldPrice;
                $priceHistory->price = $request->price;
                $priceHistory->changed_by = $request->user()->id;
                $priceHistory->save();
            }
            
            if ($request->has('category_id')) {
                $product->category_id = $request->category_id;
            }
            
            if ($request->has('sku')) {
                $product->sku = $request->sku;
            }
            
            if ($request->has('status')) {
                $product->status = $request->status;
            }
            
            if ($request->has('weight')) {
                $product->weight = $request->weight;
            }
            
            if ($request->has('dimensions')) {
                $product->dimensions = $request->dimensions;
            }
            
            if ($request->has('featured')) {
                $product->featured = $request->featured;
            }
            
            $product->updated_by = $request->user()->id;
            $product->save();
            
            // Update inventory if stock is provided
            if ($request->has('stock')) {
                $inventory = Inventory::firstOrNew(['product_id' => $product->id]);
                $inventory->stock = $request->stock;
                $inventory->updated_by = $request->user()->id;
                $inventory->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'inventory', 'images'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Soft delete the product
            $product->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }
}