<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of support tickets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tickets = SupportTicket::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.support_tickets.index', compact('tickets'));
    }

    /**
     * Display the specified ticket.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages'])->findOrFail($id);
        
        return view('admin.support_tickets.show', compact('ticket'));
    }

    /**
     * Reply to the specified ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
        
        $ticket = SupportTicket::findOrFail($id);
        
        // Add reply message logic here
          return redirect()->route('support-tickets.show', $ticket->id)
            ->with('success', 'Reply sent successfully!');
    }

    /**
     * Update the status of the ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);
        
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'status' => $request->status,
        ]);
          return redirect()->route('support-tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully!');
    }

    /**
     * Close the ticket.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function close($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'status' => 'closed',
        ]);
          return redirect()->route('support-tickets.index')
            ->with('success', 'Ticket closed successfully!');
    }
}
