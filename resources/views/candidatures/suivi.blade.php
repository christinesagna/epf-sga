@extends('layouts.app')

@section('title', 'Suivi de ma candidature | EPF Africa')

@section('content')
    <section style="min-height:calc(100vh - 72px); background:linear-gradient(135deg,#fdf1f6 0%,#f8fafc 52%,#f1edfa 100%); padding:48px 16px;">
        <div style="max-width:900px; margin:0 auto;">
            <div style="border-radius:28px; background:#260052; padding:32px; color:#fff; box-shadow:0 22px 55px rgba(38,0,82,.16);">
                <p style="margin:0; color:#fca5b5; font-size:.75rem; font-weight:800; letter-spacing:.18em; text-transform:uppercase;">Suivi de candidature</p>
                <h1 style="margin:10px 0 0; font-size:clamp(1.8rem,4vw,2.6rem);">
                    Bonjour {{ $candidature->candidat->prenom }}
                </h1>
                <p style="margin:12px 0 0; color:#e9ddf7; line-height:1.7;">
                    Retrouvez ici l’évolution de votre demande d’admission à EPF Africa.
                </p>
            </div>

            <div style="display:grid; gap:20px; margin-top:24px; grid-template-columns:repeat(auto-fit,minmax(250px,1fr));">
                <article style="border:1px solid #e6def1; border-radius:20px; background:#fff; padding:24px; box-shadow:0 10px 30px rgba(38,0,82,.06);">
                    <p style="margin:0; color:#706784; font-size:.75rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">Formation demandée</p>
                    <h2 style="margin:10px 0 0; color:#260052; font-size:1.15rem;">{{ $candidature->programme?->nom ?? 'Programme indisponible' }}</h2>
                    <p style="margin:8px 0 0; color:#706784;">{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}</p>
                </article>

                <article style="border:1px solid #e6def1; border-radius:20px; background:#fff; padding:24px; box-shadow:0 10px 30px rgba(38,0,82,.06);">
                    <p style="margin:0; color:#706784; font-size:.75rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">
                        {{ $estReorientee ? 'Décision actuelle' : 'État actuel' }}
                    </p>
                    <h2 style="margin:10px 0 0; color:#e3062f; font-size:1.35rem;">
                        {{ $estReorientee ? 'Réorientation' : $candidature->statut->libelle() }}
                    </h2>
                    <p style="margin:8px 0 0; color:#706784;">
                        {{ $estReorientee ? 'Décision enregistrée le' : 'Dernière mise à jour :' }}
                        {{ $candidature->updated_at->format('d/m/Y à H:i') }}
                    </p>
                </article>
            </div>

            @if ($candidature->programme_origine_id && $candidature->programmeOrigine)
                <div style="margin-top:24px; border:1px solid #c4b5fd; border-radius:20px; background:#f5f3ff; padding:24px;">
                    <p style="margin:0; color:#6d28d9; font-size:.75rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase;">
                        Décision du jury
                    </p>
                    <h2 style="margin:8px 0 0; color:#260052; font-size:1.25rem;">
                        Votre candidature a été réorientée
                    </h2>
                    <p style="margin:10px 0 0; color:#4f4663; line-height:1.7;">
                        Après étude de votre dossier, le jury a réorienté votre candidature de
                        <strong>{{ $candidature->programmeOrigine->nom }}</strong>
                        vers <strong>{{ $candidature->programme?->nom ?? 'le nouveau programme sélectionné' }}</strong>.
                    </p>
                    <p style="margin:8px 0 0; color:#706784; font-size:.9rem; line-height:1.6;">
                        Le jury a rendu sa décision d’orientation. Votre candidature est désormais rattachée au nouveau programme indiqué ci-dessus.
                    </p>
                </div>
            @endif

            @if ($complementAttendu)
                <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-top:24px; border:1px solid #fed7aa; border-radius:20px; background:#fff7ed; padding:24px;">
                    <div>
                        <h2 style="margin:0; color:#9a3412; font-size:1.2rem;">Un complément est attendu</h2>
                        <p style="margin:8px 0 0; color:#9a3412; line-height:1.6;">Consultez la demande et transmettez les documents complémentaires.</p>
                    </div>
                    <a href="{{ route('candidatures.complements.edit', [$candidature, $token]) }}" style="display:inline-flex; border-radius:14px; background:#e3062f; color:#fff; padding:13px 20px; font-weight:800; text-decoration:none;">
                        Ajouter les documents
                    </a>
                </div>
            @endif

            @if ($candidature->statut === \App\Enums\CandidatureStatut::ADMISE)
                <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-top:24px; border:1px solid #bbf7d0; border-radius:20px; background:#f0fdf4; padding:24px;">
                    <div>
                        <h2 style="margin:0; color:#166534; font-size:1.2rem;">Votre lettre d’admission est disponible</h2>
                        <p style="margin:8px 0 0; color:#166534; line-height:1.6;">
                            Téléchargez et conservez ce document officiel au format PDF.
                        </p>
                    </div>
                    <a href="{{ route('candidatures.lettre-admission', [$candidature, $token]) }}" style="display:inline-flex; border-radius:14px; background:#166534; color:#fff; padding:13px 20px; font-weight:800; text-decoration:none;">
                        Télécharger ma lettre
                    </a>
                </div>
            @endif

            <section style="margin-top:24px; border:1px solid #e6def1; border-radius:24px; background:#fff; padding:28px; box-shadow:0 10px 30px rgba(38,0,82,.06);">
                <p style="margin:0; color:#e3062f; font-size:.75rem; font-weight:800; letter-spacing:.16em; text-transform:uppercase;">Historique</p>
                <h2 style="margin:8px 0 0; color:#260052; font-size:1.45rem;">Évolution du dossier</h2>

                <div style="display:grid; gap:12px; margin-top:22px;">
                    @forelse ($candidature->historiques as $historique)
                        <article style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:14px; border-radius:16px; background:#f8f5fc; padding:18px;">
                            <div>
                                <p style="margin:0; color:#260052; font-weight:800;">
                                    @if (($historique->metadata['action'] ?? null) === 'reorientation')
                                        Réorientation du dossier
                                    @else
                                        {{ \App\Enums\CandidatureStatut::tryFrom($historique->nouveau_statut)?->libelle() ?? $historique->nouveau_statut }}
                                    @endif
                                </p>
                                @if ($historique->commentaire)
                                    <p style="margin:7px 0 0; color:#706784; line-height:1.6;">{{ $historique->commentaire }}</p>
                                @endif
                            </div>
                            <time style="color:#706784; font-size:.8rem;">{{ $historique->created_at->format('d/m/Y H:i') }}</time>
                        </article>
                    @empty
                        <p style="margin:0; border-radius:16px; background:#f8f5fc; padding:20px; color:#706784;">
                            Votre candidature a été soumise et sera prochainement examinée.
                        </p>
                    @endforelse
                </div>
            </section>

            <p style="margin:22px 0 0; color:#706784; font-size:.8rem; line-height:1.6; text-align:center;">
                Ce lien est personnel. Ne le partagez pas.
            </p>
        </div>
    </section>
@endsection
