<?php

namespace App\Enums;

enum TicketSentiment: string
{
    case POSITIVE = 'Positive';
    case NEUTRAL = 'Neutral';
    case NEGATIVE = 'Negative';
}
