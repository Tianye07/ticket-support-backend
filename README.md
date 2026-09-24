# Ticket Support Portal – Backend

A Laravel REST API for managing support tickets: you can create, list, filter, view, update and delete them.

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL

## Getting Started

1. Install dependencies:

    ```bash
    composer install
    npm install
    ```

2. Create the environment file and app key:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3. Create an empty MySQL database, then update the database settings in `.env`:

    ```env
    DB_CONNECTION=ticketsupportdb
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_USERNAME=root
    DB_PASSWORD=
    ```

    > `DB_CONNECTION` must be `ticketsupportdb`. The migration and controller use this connection name.

4. Run the migration and seed the sample tickets:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5. Start the server:

    ```bash
    php artisan serve
    ```

    The API is now available at `http://localhost:8000/api/app/tickets`.

## Running Tests

The feature tests (Pest) call the API endpoints and check the responses and the database. They run against a **separate MySQL database, `ticketsupportdb_test`**, which is wiped and re-migrated for every test.

1. Create the empty test database once, using the same MySQL server and credentials as in `.env`:

    ```sql
    CREATE DATABASE ticketsupportdb_test;
    ```

2. Run the tests:

    ```bash
    composer test
    ```

    `composer test` clears the config cache first, then runs `php artisan test`.

> **Safety check:** if the configured database name does not end in `_test`, the tests refuse to run. This stops them wiping your real data, e.g. when the config is cached.

What `tests/Feature/TicketApiTest.php` covers:

| Endpoint | Cases |
| --- | --- |
| `GET /tickets` | Response format, empty list, filtering by status and priority, partial title search |
| `POST /tickets` | Creates and saves a ticket, optional description, `422` for missing fields, unsupported priority or status, and values that are too long |
| `GET /tickets/{id}` | Returns the ticket, `404` with error code `0001` when it doesn't exist |
| `PUT /tickets/{id}` | Changes status (including reopening a resolved ticket), `422` leaves the ticket unchanged, `404` |
| `DELETE /tickets/{id}` | Removes the ticket, `404` |

## Sample Data

`php artisan db:seed` runs `TicketSeeder`, which inserts 12 sample tickets. Together they cover every combination of:

| Status        | Priority |
| ------------- | -------- |
| `open`        | `high`   |
| `in_progress` | `medium` |
| `resolved`    | `low`    |

Running the seeder again is safe. Tickets that already exist (same `title` and `requester_name`) are skipped, so no duplicates are created.

To reset the database to only the sample data:

```bash
php artisan migrate:fresh --seed
```

## API Endpoints

Base URL: `http://localhost:8000/api/app`

| Method   | Endpoint        | Description                     |
| -------- | --------------- | ------------------------------- |
| `GET`    | `/tickets`      | List tickets (supports filters) |
| `POST`   | `/tickets`      | Create a ticket                 |
| `GET`    | `/tickets/{id}` | Get a single ticket             |
| `PUT`    | `/tickets/{id}` | Update a ticket                 |
| `DELETE` | `/tickets/{id}` | Delete a ticket                 |

### Filters (`GET /tickets`)

Each filter is optional, and you can combine them:

| Query param | Match                          | Example          |
| ----------- | ------------------------------ | ---------------- |
| `title`     | Partial match (`LIKE %value%`) | `?title=login`   |
| `priority`  | Exact match                    | `?priority=high` |
| `status`    | Exact match                    | `?status=open`   |

Example: `GET /api/app/tickets?status=open&priority=high`

### Create / Update body

| Field            | Rules                                              |
| ---------------- | -------------------------------------------------- |
| `title`          | required, string, max 100                          |
| `description`    | optional, string, max 350                          |
| `priority`       | required, one of `low`, `medium`, `high`           |
| `status`         | required, one of `open`, `in_progress`, `resolved` |
| `requester_name` | required, string, max 100                          |

```bash
curl -X POST http://localhost:8000/api/app/tickets \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title":"Cannot reset password","description":"Reset link expired","priority":"high","status":"open","requester_name":"John Doe"}'
```

### Responses

Success:

```json
{
    "code": "0",
    "success": true,
    "data": {
        "id": 1,
        "title": "Cannot reset password",
        "description": "Reset link expired",
        "priority": "high",
        "status": "open",
        "requester_name": "John Doe",
        "createdAt": "2026-09-25T10:00:00.000000Z",
        "updatedAt": "2026-09-25T10:00:00.000000Z"
    }
}
```

Ticket not found (`404`):

```json
{
    "code": "0001",
    "success": false,
    "message": "Ticket not found."
}
```

Invalid input returns Laravel's standard `422` validation error response.

## How It Works

- **Routes** (`routes/api.php`): every ticket route goes to `TicketController` and is prefixed with `api/app` (set up in `bootstrap/app.php`).
- **Controller** (`app/Http/Controllers/TicketController.php`):
    - `index`: builds the query with `when()`, so a filter is applied only when that query param is sent.
    - `store` / `update`: validate the request, then save inside a database transaction. If anything fails, the transaction is rolled back.
    - `show` / `update` / `delete`: throw `NotFoundTicketException` when the ticket ID does not exist.
    - `delete`: removes the ticket inside a transaction.
- **Allowed values** (`app/Constants/Ticket/`): the valid priorities and statuses are kept as constants, and validation uses them.
- **Response format**:
    - `Controller::responseSuccess()` wraps every successful response in the same `code` / `success` / `data` structure.
    - `TicketResource` controls which ticket fields are returned.
- **Error handling** (`app/Exceptions/NotFoundTicketException.php`): renders the `404` JSON response. Requests to `api/*` always get JSON errors, not HTML pages.
- **Seeder** (`database/seeders/TicketSeeder.php`): inserts the sample tickets, skips any that already exist, and prints how many records it created.
