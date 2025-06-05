<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    use AuditLoggable;
      /**
     * Display a listing of support tickets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Log the view action
        $this->logCustomAction('view_support_tickets_list', null, 'Accessed support tickets list');
        
        $tickets = SupportTicket::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.support_tickets.index', compact('tickets'));
    }    /**
     * Display the specified ticket.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages'])->findOrFail($id);
        
        // Log the view action
        $this->logCustomAction('view_support_ticket_details', null, "Viewed support ticket details (ID: {$id})");
        
        return view('admin.support_tickets.show', compact('ticket'));
    }    /**
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
        
        try {
            $ticket = SupportTicket::findOrFail($id);
            
            // Add reply message logic here
            // When implemented, this would save the reply to the database
            
            // Log the reply action
            $this->logCustomAction('reply_support_ticket', null, "Replied to support ticket (ID: {$id})");
            
            return redirect()->route('admin.support-tickets.show', $ticket->id)
                ->with('success', 'Reply sent successfully!');
        } catch (\Exception $e) {
            // Log the error
            $this->logCustomAction('support_ticket_reply_failed', null, "Failed to reply to support ticket (ID: {$id}): " . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send reply: ' . $e->getMessage());
        }
    }    /**
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
        
        try {
            $ticket = SupportTicket::findOrFail($id);
            $oldStatus = $ticket->status;
            $newStatus = $request->status;
            
            $ticket->update([
                'status' => $newStatus,
            ]);
            
            // Log the status update action
            $this->logCustomAction('update_support_ticket_status', null, "Updated support ticket status from '{$oldStatus}' to '{$newStatus}' (ID: {$id})");
            
            return redirect()->route('admin.support-tickets.show', $ticket->id)
                ->with('success', 'Ticket status updated successfully!');
        } catch (\Exception $e) {
            // Log the error
            $this->logCustomAction('support_ticket_status_update_failed', null, "Failed to update support ticket status (ID: {$id}): " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update ticket status: ' . $e->getMessage());
        }
    }    /**
     * Close the ticket.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function close($id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'closed',
            ]);
            
            // Log the close action
            $this->logCustomAction('close_support_ticket', null, "Closed support ticket (previous status: '{$oldStatus}', ID: {$id})");
            
            return redirect()->route('admin.support-tickets.index')
                ->with('success', 'Ticket closed successfully!');
        } catch (\Exception $e) {
            // Log the error
            $this->logCustomAction('support_ticket_close_failed', null, "Failed to close support ticket (ID: {$id}): " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to close ticket: ' . $e->getMessage());
        }
    }
}
