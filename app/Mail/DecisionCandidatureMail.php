<?php

namespace App\Mail;

use App\Enums\CandidatureStatut;
use App\Models\Candidature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class DecisionCandidatureMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Candidature $candidature,
        public ?string $motif = null,
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
            subject: $this->candidature->statut === CandidatureStatut::ADMISE
                ? 'Votre candidature à EPF Africa est admise'
                : 'Décision concernant votre candidature à EPF Africa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.candidatures.decision',
            text: 'mail.candidatures.decision-text',
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
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->candidature->statut !== CandidatureStatut::ADMISE) {
            return [];
        }

        return [
            Attachment::fromData(
                fn (): string => $this->lettreAdmission(),
                'lettre-admission-'.$this->candidature->id.'.pdf',
            )->withMime('application/pdf'),
        ];
    }

    private function lettreAdmission(): string
    {
        $this->candidature->loadMissing([
            'candidat',
            'programme',
            'programmeNiveau.niveau',
        ]);
        $decision = $this->candidature->historiques()
            ->where('nouveau_statut', CandidatureStatut::ADMISE->value)
            ->latest()
            ->first();
        $dateDecision = $decision?->created_at ?? $this->candidature->updated_at;
        $logo = file_get_contents(public_path('images/logo-epf-africa.jpg'));

        return Pdf::loadView('pdf.lettre-admission', [
            'candidature' => $this->candidature,
            'dateDecision' => $dateDecision,
            'reference' => sprintf(
                'EPF-%s-%06d',
                $dateDecision->format('Y'),
                $this->candidature->id,
            ),
            'logoDataUri' => 'data:image/jpeg;base64,'.base64_encode($logo),
        ])
            ->setPaper('a4')
            ->output();
    }
}
