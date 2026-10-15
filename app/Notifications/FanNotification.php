<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * A short message to a fan, delivered by push and/or email depending on their settings.
 */
abstract class FanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract public function title(): string;

    abstract public function body(): string;

    abstract public function url(): string;

    public function actionLabel(): string
    {
        return 'Ansehen';
    }

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        $channels = [];

        if ($notifiable->notify_by_push && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->notify_by_mail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->greeting('Hoi '.$notifiable->displayName().'!')
            ->line($this->body())
            ->action($this->actionLabel(), $this->url())
            ->salutation('Hopp Winti!');
    }

    public function toWebPush(User $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title())
            ->body($this->body())
            ->icon(asset('icons/icon-192.png'))
            ->badge(asset('icons/badge-96.png'))
            ->lang('de-CH')
            ->tag(static::class.':'.$this->tagKey())
            ->data(['url' => $this->url()]);
    }

    /**
     * Notifications with the same tag replace each other on the device.
     */
    protected function tagKey(): string
    {
        return (string) spl_object_id($this);
    }
}
