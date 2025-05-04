<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the user's support tickets.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tickets = $user->supportTickets()->latest()->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Store a newly created support ticket.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|string|in:low,medium,high',
            'type' => 'required|string|in:question,complaint,return,refund,other',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        $ticket = new SupportTicket([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'type' => $request->type,
            'status' => 'open',
            'order_id' => $request->order_id,
        ]);
        
        $ticket->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    /**
     * Display the specified support ticket.
     *
     * @param Request $request
     * @param SupportTicket $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        
        // Ensure the ticket belongs to the authenticated user
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $ticket->load(['responses', 'order']);
        
        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Update the specified support ticket.
     *
     * @param Request $request
     * @param SupportTicket $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Ensure the ticket belongs to the authenticated user
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Prevent updating closed tickets
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update a closed ticket',
            ], 400);
        }
        
        // Add a response to the ticket
        $response = $ticket->responses()->create([
            'user_id' => $user->id,
            'message' => $request->message,
            'is_from_admin' => false,
        ]);
        
        // Reopen the ticket if it was pending
        if ($ticket->status === 'pending') {
            $ticket->status = 'open';
            $ticket->save();
        }
        
        $ticket->load(['responses', 'order']);
        
        return response()->json([
            'success' => true,
            'message' => 'Support ticket updated successfully',
            'data' => $ticket
        ]);
    }

    /**
     * Close the specified support ticket.
     *
     * @param Request $request
     * @param SupportTicket $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        
        // Ensure the ticket belongs to the authenticated user
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Prevent closing already closed tickets
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is already closed',
            ], 400);
        }
        
        $ticket->status = 'closed';
        $ticket->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Support ticket closed successfully',
            'data' => $ticket
        ]);
    }
}