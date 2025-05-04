<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Apply search filter if provided
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply category filter if provided
        if ($request->has('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Apply price range filter if provided
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        $allowedSortFields = ['name', 'price', 'created_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $products = $query->with(['categories', 'images'])->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        // Load product with related data
        $product->load(['categories', 'images', 'variants', 'reviews' => function($query) {
            $query->latest()->limit(5);
        }]);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Get related products for a given product.
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRelatedProducts(Product $product)
    {
        // Get category IDs of the current product
        $categoryIds = $product->categories()->pluck('categories.id');

        // Get products that share categories with the current product, excluding the current one
        $relatedProducts = Product::whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $product->id)
            ->with(['images'])
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedProducts
        ]);
    }

    /**
     * Get featured products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeaturedProducts()
    {
        $featuredProducts = Product::where('is_featured', true)
            ->with(['images'])
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featuredProducts
        ]);
    }

    /**
     * Get new arrivals (most recently added products).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewArrivals()
    {
        $newArrivals = Product::latest()
            ->with(['images'])
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $newArrivals
        ]);
    }

    /**
     * Get best selling products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBestSellers()
    {
        $bestSellers = Product::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->with(['images'])
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bestSellers
        ]);
    }
}