<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as Base;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Base
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Weinstein-Passwort zurücksetzen')
                    ->greeting('Guten Tag,')
                    ->line('Mit folgendem Link können Sie Ihr Password zurücksetzen:')
                    ->action('Password zurücksetzen', url(config('app.url').route('password.reset-form', $this->token, false)))
                    ->salutation('Weinstein');
    }
}
