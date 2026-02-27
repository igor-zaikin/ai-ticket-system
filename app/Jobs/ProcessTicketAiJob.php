<?php

namespace App\Jobs;

use App\Services\ProcessTicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTicketAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $ticketId,
    ) {
    }

    public function handle(ProcessTicketService $service): void
    {
        $service->handle($this->ticketId);
    }
}
