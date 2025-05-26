<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $searchQuery = $request->input('search');
        $statusFilter = $request->input('status');
        
        $customers = User::withCount('orders')
            ->when($searchQuery, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter === 'active', function ($query) {
                return $query->where('is_active', true);
            })
            ->when($statusFilter === 'inactive', function ($query) {
                return $query->where('is_active', false);
            })
            ->latest()
            ->paginate(15);
        
        return view('admin.customers.index', compact('customers', 'searchQuery', 'statusFilter'));
    }

    /**
     * Display the specified customer.
     *
     * @param  \App\Models\User  $customer
     * @return \Illuminate\View\View
     */
    public function show(User $customer)
    {
        $customer->load('orders', 'reviews', 'wishlist', 'addresses');
        
        $orderStats = [
            'total' => $customer->orders->count(),
            'completed' => $customer->orders->where('status', 'Delivered')->count(),
            'pending' => $customer->orders->whereIn('status', ['Pending', 'Processing'])->count(),
            'cancelled' => $customer->orders->where('status', 'Cancelled')->count(),
            'total_spent' => $customer->orders->where('status', '!=', 'Cancelled')->sum('total_amount'),
        ];
        
        $recentOrders = Order::where('user_id', $customer->id)
            ->with('orderItems.product')
            ->latest()
            ->take(5)
            ->get();
        
        return view('admin.customers.show', compact('customer', 'orderStats', 'recentOrders'));
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
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users')->ignore($customer->id),
            ],
            'username' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('users')->ignore($customer->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);
        
        // Set default active status if not provided
        if (!isset($validatedData['is_active'])) {
            $validatedData['is_active'] = false;
        }

        $customer->update($validatedData);

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
        ]);

        $customer->update([
            'password' => Hash::make($validatedData['password']),
        ]);

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Customer password reset successfully!');
    }

    /**
     * Toggle the active status of the specified customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(User $customer)
    {
        $customer->update([
            'is_active' => !$customer->is_active,
        ]);

        $status = $customer->is_active ? 'activated' : 'deactivated';

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
    {
        // Check if customer has orders before allowing deletion
        if ($customer->orders()->where('status', '!=', 'Cancelled')->exists()) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with active orders.');
        }

        // Soft delete
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}
