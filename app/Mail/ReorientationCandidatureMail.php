<?php

namespace App\Mail;

use App\Models\Candidature;
use App\Models\Programme;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class ReorientationCandidatureMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Candidature $candidature,
        public Programme $ancienProgramme,
        public string $motif,
    ) {
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

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réorientation de votre candidature à EPF Africa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.candidatures.reorientation',
            text: 'mail.candidatures.reorientation-text',
            with: [
                'urlSuivi' => route('candidatures.suivi', [
                    $this->candidature,
                    $this->candidature->edit_token,
                ]),
                'logoUrl' => 'cid:logo-epf-africa@epf-sga',
            ],
        );
    }
}
