<?php

use App\Models\Ticket;

const TICKETS_URL = '/api/app/tickets';

function validTicketPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Cannot reset password',
        'description' => 'The reset link has expired.',
        'priority' => 'high',
        'status' => 'open',
        'requester_name' => 'John Doe',
    ], $overrides);
}

describe('GET /tickets', function () {
    test('lists all tickets in the standard response envelope', function () {
        Ticket::factory()->count(3)->create();

        $this->getJson(TICKETS_URL)
            ->assertOk()
            ->assertJson(['code' => '0', 'success' => true])
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'description', 'priority', 'status', 'requester_name', 'createdAt', 'updatedAt']],
            ]);
    });

    test('returns an empty list when there are no tickets', function () {
        $this->getJson(TICKETS_URL)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('filters by status and priority', function () {
        Ticket::factory()->create(['status' => 'open', 'priority' => 'high']);
        Ticket::factory()->create(['status' => 'open', 'priority' => 'low']);
        Ticket::factory()->create(['status' => 'resolved', 'priority' => 'high']);

        $this->getJson(TICKETS_URL.'?status=open&priority=high')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'open')
            ->assertJsonPath('data.0.priority', 'high');
    });

    test('filters by partial title match', function () {
        Ticket::factory()->create(['title' => 'Unable to login']);
        Ticket::factory()->create(['title' => 'Printer is broken']);

        $this->getJson(TICKETS_URL.'?title=login')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Unable to login');
    });
});

describe('POST /tickets', function () {
    test('creates a ticket and stores it in the database', function () {
        $this->postJson(TICKETS_URL, validTicketPayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Cannot reset password')
            ->assertJsonPath('data.requester_name', 'John Doe');

        $this->assertDatabaseHas('tickets', [
            'title' => 'Cannot reset password',
            'priority' => 'high',
            'status' => 'open',
            'requester_name' => 'John Doe',
        ]);
    });

    test('allows the description to be left out', function () {
        $this->postJson(TICKETS_URL, validTicketPayload(['description' => null]))
            ->assertOk()
            ->assertJsonPath('data.description', null);
    });

    test('rejects a ticket with missing required fields', function () {
        $this->postJson(TICKETS_URL, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'priority', 'status', 'requester_name']);

        $this->assertDatabaseCount('tickets', 0);
    });

    test('rejects an unsupported priority or status', function () {
        $this->postJson(TICKETS_URL, validTicketPayload(['priority' => 'urgent', 'status' => 'closed']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['priority', 'status']);
    });

    test('rejects values longer than the allowed length', function () {
        $this->postJson(TICKETS_URL, validTicketPayload([
            'title' => str_repeat('a', 101),
            'description' => str_repeat('a', 351),
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description']);
    });
});

describe('GET /tickets/{id}', function () {
    test('returns a single ticket', function () {
        $ticket = Ticket::factory()->create();

        $this->getJson(TICKETS_URL."/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.title', $ticket->title);
    });

    test('returns 404 when the ticket does not exist', function () {
        $this->getJson(TICKETS_URL.'/999999')
            ->assertNotFound()
            ->assertExactJson([
                'code' => '0001',
                'success' => false,
                'message' => 'Ticket not found.',
            ]);
    });
});

describe('PUT /tickets/{id}', function () {
    test('updates a ticket, including moving it to another status', function () {
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $this->putJson(TICKETS_URL."/{$ticket->id}", validTicketPayload(['status' => 'in_progress']))
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        expect($ticket->fresh()->status)->toBe('in_progress');
    });

    test('allows a resolved ticket to be reopened', function () {
        $ticket = Ticket::factory()->create(['status' => 'resolved']);

        $this->putJson(TICKETS_URL."/{$ticket->id}", validTicketPayload(['status' => 'open']))
            ->assertOk()
            ->assertJsonPath('data.status', 'open');
    });

    test('rejects invalid data and leaves the ticket unchanged', function () {
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $this->putJson(TICKETS_URL."/{$ticket->id}", validTicketPayload(['status' => 'closed']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        expect($ticket->fresh()->status)->toBe('open');
    });

    test('returns 404 when the ticket does not exist', function () {
        $this->putJson(TICKETS_URL.'/999999', validTicketPayload())
            ->assertNotFound()
            ->assertJsonPath('code', '0001');
    });
});

describe('DELETE /tickets/{id}', function () {
    test('deletes a ticket', function () {
        $ticket = Ticket::factory()->create();

        $this->deleteJson(TICKETS_URL."/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    });

    test('returns 404 when the ticket does not exist', function () {
        $this->deleteJson(TICKETS_URL.'/999999')
            ->assertNotFound()
            ->assertJsonPath('code', '0001');
    });
});
