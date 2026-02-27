<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketRequestService;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketRequestService $ticketRequestService,
    ) {
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketRequestService->handle($request->validated());

        return (new TicketResource($ticket))
            ->additional([
                'message' => 'Ticket created.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse|TicketResource
    {
        $ticket = Ticket::query()->find($id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        return new TicketResource($ticket);
    }
}
