<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLinkNotification extends Notification
{
	use Queueable;

	public function __construct(public string $url) {}

	/**
	 * @return array<int, string>
	 */
	public function via(object $notifiable): array
	{
		return ['mail'];
	}

	public function toMail(object $notifiable): MailMessage
	{
		return (new MailMessage)
			->subject('Dein Login für Got a ticket?')
			->greeting('Hoi!')
			->line('Klick auf den Button, um dich einzuloggen. Der Link ist 30 Minuten gültig.')
			->action('Einloggen', $this->url)
			->line('Du hast keinen Login angefordert? Dann kannst du diese E-Mail ignorieren.')
			->salutation('Hopp Winti!');
	}
}
