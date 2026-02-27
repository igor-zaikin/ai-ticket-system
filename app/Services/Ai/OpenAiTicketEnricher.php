<?php

namespace App\Services\Ai;

use App\Enums\TicketCategory;
use App\Enums\TicketSentiment;
use App\Enums\TicketUrgency;
use App\Services\Ai\Dto\TicketEnrichmentRequestDto;
use App\Services\Ai\Dto\TicketEnrichmentResultDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiTicketEnricher
{
    private string $baseUrl;
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('ai.tickets.base_url'), '/');
        $this->apiKey = (string) config('ai.tickets.api_key');
        $this->model = (string) config('ai.tickets.model');
        $this->timeout = (int) config('ai.tickets.timeout', 45);
    }

    public function enrich(TicketEnrichmentRequestDto $request): TicketEnrichmentResultDto
    {
        $systemPrompt = $this->buildSystemPrompt();
        $schema = $this->buildStructuredSchema();
        $userPrompt = $this->buildUserPrompt($request);

        try {
            $response = Http::baseUrl($this->baseUrl)
                            ->withToken($this->apiKey)
                            ->acceptJson()
                            ->timeout($this->timeout)
                            ->retry(2, 1000)
                            ->post('/chat/completions', [
                                'model' => $this->model,
                                'temperature' => 0.2,
                                'messages' => [
                                    [
                                        'role' => 'system',
                                        'content' => $systemPrompt,
                                    ],
                                    [
                                        'role' => 'user',
                                        'content' => $userPrompt,
                                    ],
                                ],
                                'response_format' => [
                                    'type' => 'json_schema',
                                    'json_schema' => $schema,
                                ],
                            ]);

            if ($response->failed()) {
                Log::warning('OpenAI ticket enrichment request failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return new TicketEnrichmentResultDto(
                    category: null,
                    sentiment: null,
                    urgency: null,
                    reply: null,
                    isError: true,
                    issue: 'OpenAI request failed.',
                    rawResponse: $response->json(),
                );
            }

            $rawContent = data_get($response->json(), 'choices.0.message.content');

            if (!is_string($rawContent) || trim($rawContent) === '') {
                throw new \RuntimeException('OpenAI returned empty structured content.');
            }

            $payload = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($payload)) {
                throw new \RuntimeException('Structured payload is not an array.');
            }

            return $this->toResultDto($payload, $response->json());
        } catch (Throwable $e) {
            Log::warning('OpenAI ticket enrichment exception', [
                'error' => $e->getMessage(),
            ]);

            return new TicketEnrichmentResultDto(
                category: null,
                sentiment: null,
                urgency: null,
                reply: null,
                isError: true,
                issue: $e->getMessage(),
                rawResponse: null,
            );
        }
    }

    private function buildSystemPrompt(): string
    {
        return view('ai::tickets.prompt')->render();
    }

    private function buildUserPrompt(TicketEnrichmentRequestDto $request): string
    {
        return <<<PROMPT
Ticket ID: {$request->ticketId}
Title: {$request->title}

Description:
{$request->description}
PROMPT;
    }

    private function buildStructuredSchema(): array
    {
        $path = resource_path('ai/tickets/response.json');
        $decoded = json_decode((string) file_get_contents($path), true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid ticket response schema.');
        }

        return $decoded;
    }

    private function toResultDto(array $payload, ?array $rawResponse): TicketEnrichmentResultDto
    {
        $type = (string) ($payload['type'] ?? 'error');

        if ($type === 'error') {
            $issue = trim((string) ($payload['issue'] ?? ''));

            if ($issue === '') {
                $issue = 'AI returned an error without details.';
            }

            return new TicketEnrichmentResultDto(
                category: null,
                sentiment: null,
                urgency: null,
                reply: null,
                isError: true,
                issue: $issue,
                rawResponse: $rawResponse,
            );
        }

        $category = trim((string) ($payload['category'] ?? ''));
        $sentiment = trim((string) ($payload['sentiment'] ?? ''));
        $urgency = trim((string) ($payload['urgency'] ?? ''));
        $reply = trim((string) ($payload['reply'] ?? ''));

        $this->assertValidCategory($category);
        $this->assertValidSentiment($sentiment);
        $this->assertValidUrgency($urgency);

        if ($reply === '') {
            throw new \RuntimeException('Schema violation: reply is required for enrichment.');
        }

        return new TicketEnrichmentResultDto(
            category: $category,
            sentiment: $sentiment,
            urgency: $urgency,
            reply: $reply,
            isError: false,
            issue: '',
            rawResponse: $rawResponse,
        );
    }

    private function assertValidCategory(string $category): void
    {
        $allowed = array_map(
            static fn (TicketCategory $case) => $case->value,
            TicketCategory::cases()
        );

        if (!in_array($category, $allowed, true)) {
            throw new \RuntimeException("Invalid category returned by AI: {$category}");
        }
    }

    private function assertValidSentiment(string $sentiment): void
    {
        $allowed = array_map(
            static fn (TicketSentiment $case) => $case->value,
            TicketSentiment::cases()
        );

        if (!in_array($sentiment, $allowed, true)) {
            throw new \RuntimeException("Invalid sentiment returned by AI: {$sentiment}");
        }
    }

    private function assertValidUrgency(string $urgency): void
    {
        $allowed = array_map(
            static fn (TicketUrgency $case) => $case->value,
            TicketUrgency::cases()
        );

        if (!in_array($urgency, $allowed, true)) {
            throw new \RuntimeException("Invalid urgency returned by AI: {$urgency}");
        }
    }
}
