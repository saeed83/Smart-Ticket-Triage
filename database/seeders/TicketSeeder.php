<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 50 highly realistic tickets using the factory.
        // (Note: The TicketFactory assigns 'user_id' => 1 by default,
        // which perfectly matches the Admin account we just built in the UserSeeder).
        Ticket::factory()->count(50)->create();
    }
}
