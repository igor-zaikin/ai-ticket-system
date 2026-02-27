<?php

namespace App\Models;

use App\Enums\TicketAiStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketSentiment;
use App\Enums\TicketStatus;
use App\Enums\TicketUrgency;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'category',
        'sentiment',
        'urgency',
        'suggested_reply',
        'ai_status',
        'ai_issue',
        'ai_raw_response',
        'ai_processed_at',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'category' => TicketCategory::class,
        'sentiment' => TicketSentiment::class,
        'urgency' => TicketUrgency::class,
        'ai_status' => TicketAiStatus::class,
        'ai_raw_response' => 'array',
        'ai_processed_at' => 'datetime',
    ];
}
