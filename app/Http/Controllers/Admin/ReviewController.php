<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    use AuditLoggable;    /**
     * Display a listing of product reviews.
     *
     * @return \Illuminate\Http\Response
     */    public function index(Request $request)
    {
        // Log reviews listing access with filter details
        $filterDetails = collect([
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'per_page' => $request->input('per_page', 15)
        ])->filter()->toArray();
        
        $this->logCustomAction(
            'reviews_list_viewed',
            null,
            'Viewed reviews listing' . ($filterDetails ? ' with filters: ' . json_encode($filterDetails) : '')
        );

        $searchQuery = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $perPage = $request->input('per_page', 15);
        
        // Validate per_page parameter
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $reviews = Review::with(['product', 'user'])
            ->when($searchQuery, function ($query, $search) {
                return $query->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->when($statusFilter, function ($query, $status) {
                if (in_array($status, ['pending', 'approved', 'rejected'])) {
                    return $query->where('moderation_status', $status);
                }
                return $query;
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Get pending reviews count for badge display
        $pendingReviewsCount = Review::where('moderation_status', 'pending')->count();
            
        return view('admin.reviews.index', compact('reviews', 'searchQuery', 'statusFilter', 'pendingReviewsCount'));
    }/**
     * Display the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $review = Review::with(['product', 'user'])->findOrFail($id);
        
        // Log review detail access
        $productName = $review->product ? $review->product->name : 'Unknown Product';        $userName = $review->user ? $review->user->name : 'Unknown User';
        
        $this->logCustomAction(
            'review_viewed',
            $review,
            "Viewed review details for product: {$productName} by user: {$userName} (Rating: {$review->rating}/5)"
        );
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
            'moderation_status' => 'required|in:pending,approved,rejected',
        ]);
        
        $review = Review::with(['product', 'user'])->findOrFail($id);
        
        // Store original data for audit logging
        $originalStatus = $review->moderation_status;
        $productName = $review->product ? $review->product->name : 'Unknown Product';
        $userName = $review->user ? $review->user->name : 'Unknown User';
        
        try {
            $review->update([
                'moderation_status' => $request->moderation_status,
                'moderated_by' => auth('admin')->id(),
                'moderated_at' => now(),            ]);
            
            // Log the status update
            $newStatus = $request->moderation_status;
            $oldStatus = $originalStatus;
            
            $this->logUpdate(
                $review,
                ['moderation_status' => $originalStatus],
                "Updated review status for product: {$productName} by user: {$userName} - Status: {$oldStatus} → {$newStatus}"
            );
            
            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review status updated successfully!');
                
        } catch (\Exception $e) {
            // Log error for audit trail
            $this->logCustomAction(
                'review_status_update_failed',
                $review,
                "Failed to update review status for product: {$productName} by user: {$userName} - Error: {$e->getMessage()}"
            );
            
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Error updating review status: ' . $e->getMessage());
        }
    }    /**
     * Remove the specified review from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = Review::with(['product', 'user'])->findOrFail($id);
        
        // Store data for audit logging before deletion
        $productName = $review->product ? $review->product->name : 'Unknown Product';
        $userName = $review->user ? $review->user->name : 'Unknown User';
        $rating = $review->rating;
        $comment = substr($review->comment, 0, 100) . (strlen($review->comment) > 100 ? '...' : '');
          try {
            $review->delete();
            
            // Log the deletion
            $this->logDelete(
                $review,
                "Deleted review for product: {$productName} by user: {$userName} (Rating: {$rating}/5) - Comment: {$comment}"
            );
            
            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review deleted successfully!');
                
        } catch (\Exception $e) {
            // Log error for audit trail
            $this->logCustomAction(
                'review_delete_failed',
                $review,
                "Failed to delete review for product: {$productName} by user: {$userName} - Error: {$e->getMessage()}"
            );
            
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Error deleting review: ' . $e->getMessage());
        }
    }
    
    /**
     * Approve a review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $review = Review::with(['product', 'user'])->findOrFail($id);
        
        $productName = $review->product ? $review->product->name : 'Unknown Product';
        $userName = $review->user ? $review->user->name : 'Unknown User';
        
        try {
            $review->update([
                'moderation_status' => 'approved',
                'moderated_by' => auth('admin')->id(),
                'moderated_at' => now(),
            ]);
            
            $this->logUpdate(
                $review,
                ['moderation_status' => 'pending'],
                "Approved review for product: {$productName} by user: {$userName}"
            );
            
            return redirect()->back()->with('success', 'Review approved successfully!');
        } catch (\Exception $e) {
            $this->logCustomAction(
                'review_approve_failed',
                $review,
                "Failed to approve review for product: {$productName} by user: {$userName} - Error: {$e->getMessage()}"
            );
            
            return redirect()->back()->with('error', 'Error approving review: ' . $e->getMessage());
        }
    }

    /**
     * Reject a review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($id)
    {
        $review = Review::with(['product', 'user'])->findOrFail($id);
        
        $productName = $review->product ? $review->product->name : 'Unknown Product';
        $userName = $review->user ? $review->user->name : 'Unknown User';
        
        try {
            $review->update([
                'moderation_status' => 'rejected',
                'moderated_by' => auth('admin')->id(),
                'moderated_at' => now(),
            ]);
            
            $this->logUpdate(
                $review,
                ['moderation_status' => 'pending'],
                "Rejected review for product: {$productName} by user: {$userName}"
            );
            
            return redirect()->back()->with('success', 'Review rejected successfully!');
        } catch (\Exception $e) {
            $this->logCustomAction(
                'review_reject_failed',
                $review,
                "Failed to reject review for product: {$productName} by user: {$userName} - Error: {$e->getMessage()}"
            );
            
            return redirect()->back()->with('error', 'Error rejecting review: ' . $e->getMessage());
        }
    }
}
