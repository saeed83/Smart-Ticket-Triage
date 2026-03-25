<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketRepliesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| These routes do not require an API token.
*/

Route::post('/login', [AuthController::class, 'login']);

// If you want to build a public submission form for customers later:
// Route::post('/tickets', [TicketController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
| These routes REQUIRE a valid Bearer Token from React.
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tickets CRUD
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']); // Create new ticket + AI job
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update']);

    // Ticket Replies (Nested Route)
    // URL looks like: POST /api/tickets/1/replies
    Route::post('/tickets/{ticket}/replies', [TicketRepliesController::class, 'store']);
});
