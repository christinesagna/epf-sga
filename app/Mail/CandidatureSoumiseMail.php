<?php

namespace App\Mail;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class CandidatureSoumiseMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Candidature $candidature)
    {
        $this->withSymfonyMessage(function (Email $message): void {
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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre candidature EPF Africa a bien été soumise',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.candidatures.soumise',
            text: 'mail.candidatures.soumise-text',
            with: [
                'urlSuivi' => route('candidatures.suivi', [
                    $this->candidature,
                    $this->candidature->edit_token,
                ]),
                'logoUrl' => 'cid:logo-epf-africa@epf-sga',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
