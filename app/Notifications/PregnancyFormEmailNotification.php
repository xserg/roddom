<?php

namespace App\Notifications;

use App\Mail\PregnancyForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PregnancyFormEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $html,
        private string $appLink,
        private string $appName,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('План родов')
            ->view('emails.pregnancy-form', [
                'html' => $this->html,
                'appLink' => $this->appLink,
                'appName' => $this->appName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
