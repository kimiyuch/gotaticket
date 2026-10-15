<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\URL;

class SendMagicLink
{
    public const VALID_FOR_MINUTES = 30;

    /**
     * Find or register the user for this email and send them a signed login link.
     *
     * @return string The login URL (only exposed to the UI in local development).
     */
    public function handle(string $email): string
    {
        $user = User::firstOrCreate(['email' => mb_strtolower(trim($email))]);

        $url = URL::temporarySignedRoute(
            'login.magic',
            now()->addMinutes(self::VALID_FOR_MINUTES),
            ['user' => $user->getKey()],
        );

        $user->notify(new MagicLinkNotification($url));

        return $url;
    }
}
