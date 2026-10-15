<?php

namespace App\Enums;

enum ClaimStatus: string
{
    /** Seeker has confirmed "I'm going" and waits for the giver. */
    case Requested = 'requested';

    /** Giver accepted; contact details are revealed to both. */
    case Accepted = 'accepted';

    /** Giver marked the ticket as sent. */
    case Sent = 'sent';

    /** Seeker confirmed the ticket arrived. */
    case Received = 'received';

    case Declined = 'declined';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public static function active(): array
    {
        return [self::Requested, self::Accepted, self::Sent, self::Received];
    }

    public function revealsContact(): bool
    {
        return in_array($this, [self::Accepted, self::Sent, self::Received], true);
    }
}
