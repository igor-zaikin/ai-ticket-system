<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'Open';
    case RESOLVED = 'Resolved';
}
