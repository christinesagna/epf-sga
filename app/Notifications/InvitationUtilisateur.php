<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

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
            ->view([
                'html' => 'mail.invitations.utilisateur',
                'text' => 'mail.invitations.utilisateur-text',
            ], [
                'nom' => $notifiable->name,
                'email' => $notifiable->getEmailForPasswordReset(),
                'role' => $notifiable->role?->libelle() ?? 'Utilisateur interne',
                'url' => $url,
                'logoUrl' => 'cid:logo-epf-africa@epf-sga',
                'dureeValidite' => 60,
            ])
            ->withSymfonyMessage(function (Email $message): void {
                $message->addPart(
                    DataPart::fromPath(
                        public_path('images/logo-epf-africa.jpg'),
                        'logo-epf-africa.jpg',
                        'image/jpeg',
                    )
                        ->asInline()
                        ->setContentId('logo-epf-africa@epf-sga'),
                );
            });
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
