<?php

namespace Database\Factories;

use App\Constants\Ticket\TicketPriorityConstants;
use App\Constants\Ticket\TickteStatusConstants;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TicketPriorityConstants::TICKET_PRIORITIES),
            'status' => fake()->randomElement(TickteStatusConstants::TICKET_STATUSES),
            'requester_name' => fake()->name(),
        ];
    }
}
