<?php

namespace App\Enums;

enum ClaimOutcome: string
{
	case Attended = 'attended';
	case NoShow = 'no_show';
	case TicketInvalid = 'ticket_invalid';
}
