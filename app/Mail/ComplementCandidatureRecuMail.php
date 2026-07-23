<?php

namespace App\Mail;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComplementCandidatureRecuMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Candidature $candidature) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Documents complémentaires reçus',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.candidatures.complement-recu-admission',
            text: 'mail.candidatures.complement-recu-admission-text',
            with: [
                'urlDossier' => route('admission.candidatures.show', $this->candidature),
            ],
        );
    }
}
