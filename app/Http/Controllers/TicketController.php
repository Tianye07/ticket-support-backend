<?php

namespace App\Http\Controllers;

use App\Constants\GeneralConstants;
use App\Constants\Ticket\TicketPriorityConstants;
use App\Constants\Ticket\TickteStatusConstants;
use App\Exceptions\NotFoundTicketException;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'title' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ])->validate();

        $tickets = Ticket::query()
            ->when($request->filled('title'), fn ($query) => $query->where('title', 'like', "%{$request->title}%"))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->get();

        return parent::responseSuccess(TicketResource::collection($tickets));
    }

    public function store(Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:350'],
            'priority' => ['required', 'string', Rule::in(TicketPriorityConstants::TICKET_PRIORITIES)],
            'status' => ['required', 'string', Rule::in(TickteStatusConstants::TICKET_STATUSES)],
            'requester_name' => ['required', 'string', 'max:100'],
        ])->validate();

        try {
            DB::connection(GeneralConstants::DB_NAME)->beginTransaction();

            $ticket = new Ticket;
            $ticket->title = $request->title;
            $ticket->description = $request->description;
            $ticket->priority = $request->priority;
            $ticket->status = $request->status;
            $ticket->requester_name = $request->requester_name;
            $ticket->save();

            DB::connection(GeneralConstants::DB_NAME)->commit();
        } catch (\Exception $e) {
            DB::connection(GeneralConstants::DB_NAME)->rollBack();
            throw $e;
        }

        $ticket->refresh();

        return parent::responseSuccess(new TicketResource($ticket));
    }

    public function show(string $id): JsonResponse
    {
        $ticket = Ticket::query()
            ->where('id', $id)
            ->first();
        throw_if(! $ticket, new NotFoundTicketException);

        return parent::responseSuccess(new TicketResource($ticket));
    }

    public function update(Request $request, string $id): JsonResponse
    {

        Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:350'],
            'priority' => ['required', 'string', Rule::in(TicketPriorityConstants::TICKET_PRIORITIES)],
            'status' => ['required', 'string', Rule::in(TickteStatusConstants::TICKET_STATUSES)],
            'requester_name' => ['required', 'string', 'max:100'],
        ])->validate();

        $ticket = Ticket::query()
            ->where('id', $id)
            ->first();
        throw_if(! $ticket, new NotFoundTicketException);

        try {
            DB::connection(GeneralConstants::DB_NAME)->beginTransaction();

            $ticket->title = $request->title;
            $ticket->description = $request->description;
            $ticket->priority = $request->priority;
            $ticket->status = $request->status;
            $ticket->requester_name = $request->requester_name;
            $ticket->save();

            DB::connection(GeneralConstants::DB_NAME)->commit();
        } catch (\Exception $e) {
            DB::connection(GeneralConstants::DB_NAME)->rollBack();
            throw $e;
        }

        $ticket->refresh();

        return parent::responseSuccess(new TicketResource($ticket));
    }

    public function delete(string $id): JsonResponse
    {
        $ticket = Ticket::query()
            ->where('id', $id)
            ->first();
        throw_if(! $ticket, new NotFoundTicketException);

        try {
            DB::connection(GeneralConstants::DB_NAME)->beginTransaction();

            $ticket->delete();

            DB::connection(GeneralConstants::DB_NAME)->commit();
        } catch (\Exception $e) {
            DB::connection(GeneralConstants::DB_NAME)->rollBack();
            throw $e;
        }

        return parent::responseSuccess(null);
    }
}
