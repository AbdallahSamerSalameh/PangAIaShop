<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Display a listing of product reviews.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchQuery = $request->input('search');
        $statusFilter = $request->input('status');
        
        $reviews = Review::with(['product', 'user'])
            ->when($searchQuery, function ($query, $search) {
                return $query->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->when($statusFilter, function ($query, $status) {
                if ($status === 'approved') {
                    return $query->where('is_approved', true);
                } elseif ($status === 'pending') {
                    return $query->where('is_approved', false);
                }
                return $query;
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.reviews.index', compact('reviews', 'searchQuery', 'statusFilter'));
    }

    /**
     * Display the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $review = Review::with(['product', 'user'])->findOrFail($id);
        
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Update the review approval status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_approved' => 'required|boolean',
        ]);
        
        $review = Review::findOrFail($id);
        $review->update([
            'is_approved' => $request->is_approved,
        ]);
          return redirect()->route('reviews.index')
            ->with('success', 'Review status updated successfully!');
    }

    /**
     * Remove the specified review from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
          return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully!');
    }
}
