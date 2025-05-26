<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscriber;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    /**
     * Display the about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        // Get statistics for about page
        $stats = [
            'products' => Product::where('status', 'active')->count(),
            'customers' => DB::table('users')->count(),
            'categories' => DB::table('categories')->where('is_active', true)->count(),
            'orders' => DB::table('orders')->where('status', '!=', 'cancelled')->count()
        ];
        
        // Fetch any team members if they exist in the database
        // If you don't have a team members table yet, we'll use dummy data
        try {
            $teamMembers = DB::table('team_members')->where('is_active', true)->get();
        } catch (\Exception $e) {
            // Use dummy data if table doesn't exist
            $teamMembers = collect([
                (object)[
                    'id' => 1,
                    'name' => 'John Doe',
                    'position' => 'CEO & Founder',
                    'image' => 'assets/img/team/team-1.jpg',
                    'description' => 'An experienced professional with a passion for organic food.'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'position' => 'Marketing Head',
                    'image' => 'assets/img/team/team-2.jpg',
                    'description' => 'Creative marketer with 10+ years in the organic food industry.'
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Mike Johnson',
                    'position' => 'Product Manager',
                    'image' => 'assets/img/team/team-3.jpg',
                    'description' => 'Expert in sourcing the highest quality organic products.'
                ]
            ]);
        }
        
        return view('frontend.pages.about', [
            'stats' => $stats,
            'teamMembers' => $teamMembers
        ]);
    }
    
    /**
     * Handle newsletter subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:subscribers,email',
        ]);
        
        // Create a new subscriber
        Subscriber::create([
            'email' => $request->email,
            'status' => 'active',
            'subscribed_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
}