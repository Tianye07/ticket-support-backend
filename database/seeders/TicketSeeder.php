<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Seed sample tickets covering every status and priority combination.
     */
    public function run(): void
    {
        $tickets = [
            ['title' => 'Unable to login to customer portal', 'description' => 'Getting "invalid credentials" even after resetting my password twice.', 'priority' => 'high', 'status' => 'open', 'requester_name' => 'Alice Tan', 'days_ago' => 0],
            ['title' => 'Payment page shows error 500', 'description' => 'Checkout fails with a server error when paying by credit card.', 'priority' => 'high', 'status' => 'open', 'requester_name' => 'Brandon Lee', 'days_ago' => 1],
            ['title' => 'Invoice PDF download is slow', 'description' => 'Downloading monthly invoices takes more than 30 seconds.', 'priority' => 'medium', 'status' => 'open', 'requester_name' => 'Chloe Wong', 'days_ago' => 2],
            ['title' => 'Request to update company address', 'description' => null, 'priority' => 'low', 'status' => 'open', 'requester_name' => 'Daniel Lim', 'days_ago' => 3],
            ['title' => 'Typo on the pricing page', 'description' => 'The word "subscription" is misspelled in the Pro plan card.', 'priority' => 'low', 'status' => 'open', 'requester_name' => 'Emily Ng', 'days_ago' => 4],
            ['title' => 'Password reset email not received', 'description' => 'Reset email never arrives, checked spam folder as well.', 'priority' => 'high', 'status' => 'in_progress', 'requester_name' => 'Farid Hassan', 'days_ago' => 5],
            ['title' => 'Dashboard charts not loading', 'description' => 'Sales dashboard stays on the loading spinner in Chrome.', 'priority' => 'medium', 'status' => 'in_progress', 'requester_name' => 'Grace Chen', 'days_ago' => 6],
            ['title' => 'Export report to CSV missing columns', 'description' => 'The exported CSV does not include the "Region" and "Owner" columns.', 'priority' => 'medium', 'status' => 'in_progress', 'requester_name' => 'Henry Koh', 'days_ago' => 8],
            ['title' => 'Add dark mode to settings page', 'description' => 'Feature request: allow users to toggle dark mode.', 'priority' => 'low', 'status' => 'in_progress', 'requester_name' => 'Isabel Raj', 'days_ago' => 10],
            ['title' => 'Account locked after failed login attempts', 'description' => 'Account was locked after 3 wrong attempts, needs unlock.', 'priority' => 'high', 'status' => 'resolved', 'requester_name' => 'Jason Teo', 'days_ago' => 12],
            ['title' => 'Notification emails sent twice', 'description' => 'Every ticket update triggers two identical emails.', 'priority' => 'medium', 'status' => 'resolved', 'requester_name' => 'Karen Ong', 'days_ago' => 15],
            ['title' => 'Profile picture upload fails', 'description' => 'Uploading a PNG larger than 2MB shows a generic error.', 'priority' => 'low', 'status' => 'resolved', 'requester_name' => 'Leonard Yap', 'days_ago' => 20],
        ];

        $created_records = [];

        foreach ($tickets as $ticket_data) {
            $existing_ticket = Ticket::query()
                ->where('title', $ticket_data['title'])
                ->where('requester_name', $ticket_data['requester_name'])
                ->first();

            if ($existing_ticket) {
                continue;
            }

            $timestamp = now()->subDays($ticket_data['days_ago']);

            $ticket = new Ticket;
            $ticket->title = $ticket_data['title'];
            $ticket->description = $ticket_data['description'];
            $ticket->priority = $ticket_data['priority'];
            $ticket->status = $ticket_data['status'];
            $ticket->requester_name = $ticket_data['requester_name'];
            $ticket->created_at = $timestamp;
            $ticket->updated_at = $timestamp;
            $ticket->save();

            $created_records[] = $ticket;
        }

        $this->command->comment('[TicketSeeder] - '.'Records created: '.count($created_records));
    }
}
