<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use AuditLoggable;
    /**
     * Display a listing of customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */    public function index(Request $request)
    {
        $searchQuery = $request->input('search');
        $statusFilter = $request->input('status');
        $perPage = $request->input('per_page', 25); // Default to 25 entries per page
        
        // Get customer statistics
        $totalCustomers = User::count();
        $activeCustomers = User::where('account_status', 'active')->count();
        $newThisMonth = User::whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year)
                          ->count();
        $pendingVerification = User::where('is_verified', false)->count();
        
        $customers = User::withCount(['orders' => function($query) {
                $query->whereNotIn('status', ['Cancelled']);
            }])
            ->withSum(['orders as total_spent' => function($query) {
                $query->whereNotIn('status', ['Cancelled']);
            }], 'total_amount')
            ->when($searchQuery, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter === 'active', function ($query) {
                return $query->where('account_status', 'active');
            })
            ->when($statusFilter === 'inactive', function ($query) {
                return $query->whereIn('account_status', ['suspended', 'deactivated']);
            })
            ->latest()
            ->paginate($perPage);
        
        $customerStats = [
            'total' => $totalCustomers,
            'active' => $activeCustomers,
            'new_this_month' => $newThisMonth,
            'pending_verification' => $pendingVerification
        ];
        
        return view('admin.customers.index', compact('customers', 'searchQuery', 'statusFilter', 'customerStats'));
    }/**
     * Display the specified customer.
     *
     * @param  \App\Models\User  $customer
     * @return \Illuminate\View\View     */    public function show(User $customer)
    {
        $customer->load('orders', 'reviews.product', 'wishlists');
        
        $orderStats = [
            'total' => $customer->orders->count(),
            'completed' => $customer->orders->where('status', 'Delivered')->count(),
            'pending' => $customer->orders->whereIn('status', ['Pending', 'Processing'])->count(),
            'cancelled' => $customer->orders->where('status', 'Cancelled')->count(),
            'total_spent' => $customer->orders->where('status', '!=', 'Cancelled')->sum('total_amount'),
        ];
        
        $reviewStats = [
            'total' => $customer->reviews->count(),
            'approved' => $customer->reviews->where('moderation_status', 'approved')->count(),
            'pending' => $customer->reviews->where('moderation_status', 'pending')->count(),
            'rejected' => $customer->reviews->where('moderation_status', 'rejected')->count(),
            'average_rating' => $customer->reviews->avg('rating') ?: 0,        ];
          $recentOrders = Order::where('user_id', $customer->id)
            ->with('items.product')
            ->orderBy('order_date', 'desc')
            ->take(5)
            ->get();
            
        $recentReviews = $customer->reviews()
            ->with('product')
            ->latest()
            ->take(5)
            ->get();
        
        return view('admin.customers.show', compact('customer', 'orderStats', 'reviewStats', 'recentOrders', 'recentReviews'));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param  \App\Models\User  $customer
     * @return \Illuminate\View\View
     */
    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $customer)
    {        $validatedData = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users')->ignore($customer->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'account_status' => ['in:active,suspended,deactivated'],
        ]);        // Set default account status if not provided
        if (!isset($validatedData['account_status'])) {
            $validatedData['account_status'] = 'active';
        }

        // Store original data for audit log
        $originalData = $customer->toArray();

        $customer->update($validatedData);        // Log the activity
        $this->logUpdate($customer, $originalData, "Updated customer: {$customer->username} ({$customer->email})");

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Reset the password for the specified customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request, User $customer)
    {
        $validatedData = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);        $customer->update([
            'password' => Hash::make($validatedData['password']),
        ]);        // Log the password reset activity
        $this->logCustomAction('password_reset', $customer, "Reset password for customer: {$customer->username} ({$customer->email})");

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Customer password reset successfully!');
    }

    /**
     * Toggle the active status of the specified customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $customer
     * @return \Illuminate\Http\RedirectResponse
     */    public function toggleActive(User $customer)
    {
        $oldStatus = $customer->account_status;
        $newStatus = $customer->account_status === 'active' ? 'suspended' : 'active';
        
        $customer->update([
            'account_status' => $newStatus,
        ]);

        // Log the toggle activity
        $this->logToggle($customer, 'account_status', $oldStatus, $newStatus, 
            "Toggled customer status: {$customer->username} ({$customer->email}) from {$oldStatus} to {$newStatus}");

        $status = $customer->account_status === 'active' ? 'activated' : 'suspended';

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer {$status} successfully!");
    }

    /**
     * Remove the specified customer from storage.
     * (Soft delete in most e-commerce systems)
     *
     * @param  \App\Models\User  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $customer)
    {        // Check if customer has orders before allowing deletion
        if ($customer->orders()->where('status', '!=', 'Cancelled')->exists()) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with active orders.');
        }        // Log the activity before deleting
        $this->logDelete($customer, "Deleted customer: {$customer->username} ({$customer->email})");

        // Soft delete
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}
