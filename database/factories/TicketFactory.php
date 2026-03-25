<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        // Realistic subjects to make your frontend look like a real SaaS app
        $subjects = [
            'Cannot access my account',
            'Billing overcharge on last invoice',
            'App crashes when exporting to CSV',
            'Feature request: Dark Mode',
            'Need help setting up team permissions',
            'API rate limit exceeded unexpectedly',
            'Where can I find my invoice history?',
            'Integration with Slack is failing',
        ];

        // Ensure the AI metadata matches the exact schema we fed to DeepSeek
        $category = $this->faker->randomElement(['billing', 'technical_bug', 'feature_request', 'general_inquiry']);
        $priority = $this->faker->randomElement(['low', 'medium', 'high']);

        // 80% of tickets are analyzed, 20% are still "Pending" to test your React UI spinner
        $isPending = $this->faker->boolean(20);

        return [
            'user_id' => 1, // Assuming you have a default admin user with ID 1
            'title' => $this->faker->randomElement($subjects),
            'description' => $this->faker->paragraph(3),

            // If pending, these fields are null. If analyzed, they have data.
            'status' => $isPending ? 'Pending AI Analysis' : 'Needs Agent Review',
            'category' => $category,
            'priority' => $priority,

            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}
