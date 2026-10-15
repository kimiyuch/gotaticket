<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		Gate::define('admin', fn (User $user): bool => $user->canAccessAdmin());

		/*
		 * The stylesheet tag follows right after; the extra preload only triggers
		 * "preloaded but not used" warnings after wire:navigate page swaps.
		 */
		VerifyEmail::toMailUsing(fn (object $notifiable, string $url): MailMessage => (new MailMessage)
			->subject('Bestätige deine E-Mail-Adresse')
			->greeting('Hoi '.$notifiable->displayName().'!')
			->line('Schön, bist du dabei. Bestätige noch kurz deine E-Mail-Adresse, dann kannst du Plätze freigeben und anfragen.')
			->action('E-Mail bestätigen', $url)
			->line('Du hast dich nicht registriert? Dann kannst du diese Mail ignorieren.')
			->salutation('Hopp Winti!'));

		ResetPassword::toMailUsing(fn (object $notifiable, string $token): MailMessage => (new MailMessage)
			->subject('Neues Passwort setzen')
			->greeting('Hoi '.$notifiable->displayName().'!')
			->line('Mit diesem Link setzt du ein neues Passwort. Er ist 60 Minuten gültig.')
			->action('Neues Passwort setzen', route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]))
			->line('Du hast nichts angefordert? Dann kannst du diese Mail ignorieren, dein Passwort bleibt wie es ist.')
			->salutation('Hopp Winti!'));

		Vite::usePreloadTagAttributes(fn (string $src, string $url): array|false => str_ends_with($url, '.css') ? false : []);
	}
}
