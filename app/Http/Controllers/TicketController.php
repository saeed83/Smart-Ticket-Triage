<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Jobs\AnalyzeTicketWithDeepSeek;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // This is 100% secure. The frontend cannot fake this.
        $tickets = Ticket::withCount('replies')
            ->where('user_id', $request->user()->id) // SCOPE TO THIS USER ONLY
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($tickets);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // 1. Create the ticket
        $ticket = Ticket::create([
            'user_id' => 1, // Hardcoded for this 3-week sprint, usually auth()->id()
            'customer_email' => $validated['customer_email'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'Pending AI Analysis', // AI hasn't seen it yet
        ]);

        // 2. DISPATCH THE AI JOB! 
        // This goes to SQLite/Redis and doesn't make the user wait.
        AnalyzeTicketWithDeepSeek::dispatch($ticket);

        return response()->json([
            'message' => 'Ticket created and sent to AI for triage.',
            'ticket' => $ticket
        ], 201);
    }

    // View a single ticket and its threaded conversation
    public function show(Ticket $ticket)
    {
        // Eager load the replies and the user who wrote them
        $ticket->load('replies.user');

        return response()->json($ticket);
    }

    // Update the metadata of a ticket (e.g., manually changing status or priority)
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
        ]);

        $ticket->update($validated);

        return response()->json($ticket);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
