<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketRepliesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'resolve_ticket' => 'boolean' // A checkbox from the React UI
        ]);

        // SENIOR TIP: Use Database Transactions. 
        // If the reply saves but the ticket status fails to update, 
        // the transaction rolls back so you don't get corrupted data.
        $reply = DB::transaction(function () use ($validated, $ticket, $request) {

            // 1. Create the threaded reply
            $reply = $ticket->replies()->create([
                'user_id' => $request->user()->id, // The logged-in agent from Sanctum
                'body' => $validated['body'],
            ]);

            // 2. Update the parent ticket status if the agent requested it
            if ($validated['resolve_ticket'] ?? true) {
                $ticket->update(['status' => 'Resolved']);
            } else {
                $ticket->update(['status' => 'Awaiting Customer']);
            }

            return $reply;
        });

        // In a real app, dispatch an event here to email the customer the $reply->body

        return response()->json([
            'message' => 'Reply sent successfully.',
            'reply' => $reply->load('user'),
            'ticket_status' => $ticket->status
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
