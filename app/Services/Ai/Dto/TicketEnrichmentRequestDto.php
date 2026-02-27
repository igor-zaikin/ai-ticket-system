<?php

namespace App\Services\Ai\Dto;

class TicketEnrichmentRequestDto
{
    public function __construct(
        public readonly int $ticketId,
        public readonly string $title,
        public readonly string $description,
    ) {
    }
}
