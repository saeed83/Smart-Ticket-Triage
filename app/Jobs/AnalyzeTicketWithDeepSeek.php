<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyzeTicketWithDeepSeek implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function handle(): void
    {
        $systemPrompt = <<<PROMPT
You are an expert IT helpdesk triage system. Analyze the following customer support ticket.
You MUST respond strictly in JSON format. Do not include markdown code blocks, pleasantries, or introductory text. Just output the raw JSON object.

The JSON object must perfectly match this exact schema:
{
    "category": "string (must be one of: 'billing', 'technical_bug', 'feature_request', 'general_inquiry')",
    "sentiment": "string (must be one of: 'angry', 'neutral', 'happy')",
    "priority": "string (must be one of: 'low', 'medium', 'high')",
    "draft_reply": "string (a professional, polite draft response addressing the issue)"
}
PROMPT;

        // Call the DeepSeek API
        $response = Http::withToken(env('DEEPSEEK_API_KEY'))
            ->timeout(15) // Prevent hanging jobs
            ->post('https://api.deepseek.com/chat/completions', [
                'model' => 'deepseek-chat',
                'response_format' => ['type' => 'json_object'], // FORCES JSON OUTPUT
                'temperature' => 0.1, // LOW temperature makes it deterministic and strict
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Ticket Subject: {$this->ticket->subject}\nTicket Body: {$this->ticket->body}"]
                ]
            ]);

        if ($response->successful()) {
            // Extract the raw string from DeepSeek's response
            $jsonString = $response->json('choices.0.message.content');

            // Parse the string into a PHP associative array
            $data = json_decode($jsonString, true);

            // Update your database model
            $this->ticket->update([
                'category' => $data['category'] ?? 'general_inquiry',
                'sentiment' => $data['sentiment'] ?? 'neutral',
                'priority' => $data['priority'] ?? 'medium',
                'ai_draft_reply' => $data['draft_reply'] ?? '',
                'status' => 'Needs Agent Review' // Move it out of the "Pending AI" state
            ]);
        } else {
            // Handle failures (e.g., API limits, timeouts)
            Log::error('DeepSeek API failed', ['response' => $response->body()]);
            $this->ticket->update(['status' => 'AI Analysis Failed']);
        }
    }
}
