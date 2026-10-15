<?php

namespace App\Enums;

enum OfferStatus: string
{
	case Open = 'open';
	case Taken = 'taken';
	case Withdrawn = 'withdrawn';
}
