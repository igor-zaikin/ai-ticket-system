<?php

namespace App\Services;

use App\Enums\TicketAiStatus;
use App\Models\Ticket;
use App\Services\Ai\Dto\TicketEnrichmentRequestDto;
use App\Services\Ai\OpenAiTicketEnricher;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessTicketService
{
    public function __construct(
        private readonly OpenAiTicketEnricher $enricher,
    ) {
    }

    public function handle(int $ticketId): void
    {
        $lock = Cache::lock("ticket:ai:{$ticketId}", 30);

        $lock->block(5, function () use ($ticketId): void {
            $ticket = Ticket::query()->find($ticketId);

            if (!$ticket) {
                return;
            }

            if ($ticket->ai_status === TicketAiStatus::COMPLETED) {
                return;
            }

            $ticket->forceFill([
                'ai_status' => TicketAiStatus::PROCESSING,
                'ai_issue' => null,
            ])->save();

            try {
                $result = $this->enricher->enrich(
                    new TicketEnrichmentRequestDto(
                        ticketId: $ticket->id,
                        title: $ticket->title,
                        description: $ticket->description,
                    )
                );

                if ($result->isError) {
                    $ticket->forceFill([
                        'ai_status' => TicketAiStatus::FAILED,
                        'ai_issue' => $result->issue,
                        'ai_raw_response' => $result->rawResponse,
                        'ai_processed_at' => now(),
                    ])->save();

                    return;
                }

                $ticket->forceFill([
                    'category' => $result->category,
                    'sentiment' => $result->sentiment,
                    'urgency' => $result->urgency,
                    'suggested_reply' => $result->reply,
                    'ai_status' => TicketAiStatus::COMPLETED,
                    'ai_issue' => null,
                    'ai_raw_response' => $result->rawResponse,
                    'ai_processed_at' => now(),
                ])->save();
            } catch (Throwable $e) {
                $ticket->forceFill([
                    'ai_status' => TicketAiStatus::FAILED,
                    'ai_issue' => $e->getMessage(),
                    'ai_processed_at' => now(),
                ])->save();
            }
        });
    }
}
