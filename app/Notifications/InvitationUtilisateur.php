<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationUtilisateur extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invitation.accept', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Invitation au back-office EPF Africa')
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Un accès au back-office EPF Africa vient de vous être créé.')
            ->line('Utilisez ce lien dans les 60 minutes pour définir votre mot de passe et activer votre compte.')
            ->action('Définir mon mot de passe', $url)
            ->line('Si vous n’attendiez pas cette invitation, vous pouvez ignorer ce message.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
