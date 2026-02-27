<?php

namespace App\Services;

use App\Enums\TicketAiStatus;
use App\Enums\TicketStatus;
use App\Jobs\ProcessTicketAiJob;
use App\Models\Ticket;

class TicketRequestService
{
    public function handle(array $data): Ticket
    {
        $ticket = Ticket::create([
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'status' => TicketStatus::OPEN,
            'ai_status' => TicketAiStatus::QUEUED,
        ]);

        ProcessTicketAiJob::dispatch($ticket->id);

        return $ticket->fresh();
    }
}
