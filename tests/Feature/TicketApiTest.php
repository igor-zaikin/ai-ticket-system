<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidPayloads')]
    public function test_store_returns_422_and_does_not_create_ticket_when_payload_is_invalid(
        array $payload,
        array $expectedInvalidFields
    ): void {
        $response = $this->postJson('/api/tickets', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ])
            ->assertJsonValidationErrors($expectedInvalidFields);

        $this->assertDatabaseCount('tickets', 0);
    }

    public static function invalidPayloads(): array
    {
        return [
            'empty payload' => [
                [],
                ['title', 'description'],
            ],

            'empty title and too short description' => [
                [
                    'title' => '',
                    'description' => 'short',
                ],
                ['title', 'description'],
            ],

            'title is too short' => [
                [
                    'title' => 'Hi',
                    'description' => 'This is a valid enough description.',
                ],
                ['title'],
            ],

            'description is too short' => [
                [
                    'title' => 'Valid title',
                    'description' => 'short',
                ],
                ['description'],
            ],

            'title is not a string' => [
                [
                    'title' => 123,
                    'description' => 'This is a valid enough description.',
                ],
                ['title'],
            ],

            'description is not a string' => [
                [
                    'title' => 'Valid title',
                    'description' => 12345,
                ],
                ['description'],
            ],

            'title is too long' => [
                [
                    'title' => str_repeat('a', 256),
                    'description' => 'This is a valid enough description.',
                ],
                ['title'],
            ],

            'description is too long' => [
                [
                    'title' => 'Valid title',
                    'description' => str_repeat('a', 10001),
                ],
                ['description'],
            ],
        ];
    }
}
