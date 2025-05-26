<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Display the contact page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('frontend.pages.contact');
    }
    
    /**
     * Process the contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);          // Create a support ticket from contact form        // Get user ID if authenticated, otherwise set to null
        $userId = Auth::check() ? Auth::id() : null;
        
        SupportTicket::create([
            'user_id' => $userId, // Will be null for guest users
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
            'priority' => 'medium',
            'source' => 'contact_form',
            'created_at' => now(),
        ]);
        
        // You could also send an email notification here
        
        return redirect()->back()->with('success', 'Thank you for your message. We will get back to you soon!');
    }
}