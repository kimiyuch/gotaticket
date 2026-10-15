<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class FortifyServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		$this->app->instance(LoginResponse::class, new class implements LoginResponse
		{
			public function toResponse($request): Response
			{
				return redirect()->intended(route('dashboard'))->with('toast', 'Hoi '.$request->user()->displayName().', du bist eingeloggt.');
			}
		});

		$this->app->instance(LogoutResponse::class, new class implements LogoutResponse
		{
			public function toResponse($request): Response
			{
				return redirect()->route('home')->with('toast', 'Du bist abgemeldet.');
			}
		});
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		Fortify::createUsersUsing(CreateNewUser::class);
		Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

		Fortify::loginView(fn () => view('auth.login'));
		Fortify::registerView(fn () => view('auth.register'));
		Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
		Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
		Fortify::verifyEmailView(fn () => view('auth.verify-email'));

		Fortify::authenticateUsing(function (Request $request): ?User {
			$user = User::where('email', Str::lower($request->string('email')))->first();

			if (! $user || ! $user->password || ! Hash::check($request->string('password'), $user->password)) {
				return null;
			}

			if ($user->isBlocked()) {
				throw ValidationException::withMessages(['email' => 'Dieses Konto ist gesperrt.']);
			}

			return $user;
		});

		RateLimiter::for('login', function (Request $request) {
			$throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

			return Limit::perMinute(5)->by($throttleKey);
		});
	}
}
