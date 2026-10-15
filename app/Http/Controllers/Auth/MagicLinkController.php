<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    /**
     * Log the user in via a signed link from their inbox.
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        if ($user->isBlocked()) {
            return redirect()->route('login')->withErrors(['email' => 'Dieses Konto ist gesperrt.']);
        }

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('toast', 'Hoi '.$user->displayName().', du bist eingeloggt.');
    }
}
