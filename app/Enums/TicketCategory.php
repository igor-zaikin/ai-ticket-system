<?php

namespace App\Enums;

enum TicketCategory: string
{
    case TECHNICAL = 'Technical';
    case BILLING = 'Billing';
    case GENERAL = 'General';
}
