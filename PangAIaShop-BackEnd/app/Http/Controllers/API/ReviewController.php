<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display reviews for a product.
     *
     * @param Product $product
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Product $product, Request $request)
    {
        $reviews = $product->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Store a newly created review for a product.
     *
     * @param Product $product
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Product $product, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Check if user has already reviewed this product
        $existingReview = $product->reviews()->where('user_id', $user->id)->first();
        
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 400);
        }
        
        // Check if user has purchased this product
        $hasPurchased = $user->orders()->whereHas('items', function($query) use ($product) {
            $query->where('product_id', $product->id);
        })->where('status', 'delivered')->exists();
        
        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You must purchase this product before reviewing it',
            ], 400);
        }
        
        $review = new Review([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_approved' => true, // Auto-approve reviews from verified purchasers
        ]);
        
        $review->save();
        
        // Update product average rating
        $this->updateProductRating($product);
        
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Update the specified review.
     *
     * @param Review $review
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Review $review, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string|max:1000',
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Ensure the review belongs to the authenticated user
        if ($review->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        if ($request->has('rating')) {
            $review->rating = $request->rating;
        }
        
        if ($request->has('comment')) {
            $review->comment = $request->comment;
        }
        
        if ($request->has('title')) {
            $review->title = $request->title;
        }
        
        $review->save();
        
        // Update product average rating
        $this->updateProductRating($review->product);
        
        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified review.
     *
     * @param Review $review
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review, Request $request)
    {
        $user = $request->user();
        
        // Ensure the review belongs to the authenticated user
        if ($review->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $product = $review->product;
        $review->delete();
        
        // Update product average rating
        $this->updateProductRating($product);
        
        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get reviews written by the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myReviews(Request $request)
    {
        $user = $request->user();
        
        $reviews = $user->reviews()
            ->with('product:id,name,slug')
            ->latest()
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Update the product's average rating based on its reviews.
     *
     * @param Product $product
     * @return void
     */
    private function updateProductRating(Product $product)
    {
        $avgRating = $product->reviews()->where('is_approved', true)->avg('rating') ?? 0;
        $product->average_rating = $avgRating;
        $product->save();
    }
}