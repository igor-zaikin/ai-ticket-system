<?php

namespace App\Enums;

enum TicketUrgency: string
{
    case LOW = 'Low';
    case MEDIUM = 'Medium';
    case HIGH = 'High';
}
