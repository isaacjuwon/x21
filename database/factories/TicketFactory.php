<?php

namespace Database\Factories;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Medium,
            'assigned_to' => null,
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }
}
