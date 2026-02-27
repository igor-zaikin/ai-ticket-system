<?php

namespace App\Services\Ai\Dto;

class TicketEnrichmentResultDto
{
    public function __construct(
        public readonly ?string $category,
        public readonly ?string $sentiment,
        public readonly ?string $urgency,
        public readonly ?string $reply,
        public readonly bool $isError,
        public readonly string $issue,
        public readonly ?array $rawResponse,
    ) {
    }
}
