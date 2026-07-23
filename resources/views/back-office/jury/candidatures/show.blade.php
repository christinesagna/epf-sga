<x-jury-layout>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('jury.candidatures.index') }}" class="font-semibold text-epf-red hover:underline">← Retour aux dossiers</a>
        <span class="rounded-full bg-white px-4 py-2 text-sm font-bold shadow-sm">{{ $candidature->statut->libelle() }}</span>
    </div>

    <section class="mt-5 rounded-3xl bg-epf-purple px-6 py-7 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Dossier examiné par le jury</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-5">
            <div>
                <h1 class="text-3xl font-bold sm:text-4xl">{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}</h1>
                <p class="mt-3 text-purple-100">
                    {{ $candidature->programme?->nom ?? 'Programme indisponible' }}
                    — {{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}
                </p>
            </div>
            <p class="text-sm text-purple-100">{{ $candidature->code_suivi ?: 'Dossier #'.$candidature->id }}</p>
        </div>
    </section>

    <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-5">
            <details open class="overflow-hidden rounded-2xl border border-purple-100 bg-white shadow-sm">
                <summary class="cursor-pointer list-none px-5 py-4 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100 [&::-webkit-details-marker]:hidden">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Parcours</p>
                    <h2 class="mt-1 text-xl font-bold">Informations académiques</h2>
                </summary>
                <dl class="grid gap-4 border-t border-purple-100 p-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">Dernière formation</dt>
                        <dd class="mt-1 font-semibold">{{ $candidature->derniere_formation ?: 'Non renseignée' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">Établissement</dt>
                        <dd class="mt-1 font-semibold">{{ $candidature->etablissement_provenance ?: 'Non renseigné' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">Motivation</dt>
                        <dd class="mt-2 whitespace-pre-line text-sm leading-6">{{ $candidature->motivation ?: 'Non renseignée' }}</dd>
                    </div>
                </dl>
            </details>

            <details open class="overflow-hidden rounded-2xl border border-purple-100 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100 [&::-webkit-details-marker]:hidden">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Pièces justificatives</p>
                        <h2 class="mt-1 text-xl font-bold">Documents validés par l’admission</h2>
                    </div>
                    <span class="rounded-full bg-epf-lavender px-3 py-1 text-xs font-bold">{{ $candidature->documents->count() }} document(s)</span>
                </summary>
                <div class="grid gap-3 border-t border-purple-100 p-4 sm:p-5">
                    @forelse ($candidature->documents as $document)
                        <article class="grid gap-3 rounded-xl border border-purple-100 bg-epf-lavender p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="font-bold">{{ $document->typeDocument->libelle }}</p>
                                <p class="mt-1 truncate text-sm text-epf-muted">{{ $document->original_name }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                    <span>{{ number_format($document->size / 1024, 1, ',', ' ') }} Ko</span>
                                    <span @class([
                                        'rounded-full px-2 py-1 font-bold',
                                        'bg-green-100 text-green-800' => $document->statut_validation === \App\Enums\DocumentStatutValidation::VALIDE,
                                        'bg-red-100 text-red-800' => $document->statut_validation === \App\Enums\DocumentStatutValidation::REJETE,
                                        'bg-amber-100 text-amber-900' => $document->statut_validation === \App\Enums\DocumentStatutValidation::EN_ATTENTE,
                                    ])>{{ $document->statut_validation->libelle() }}</span>
                                </div>
                            </div>
                            <a href="{{ route('jury.documents.show', $document) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-purple-200 bg-white px-4 py-2 text-sm font-semibold text-epf-purple hover:border-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">
                                Ouvrir dans le navigateur
                            </a>
                        </article>
                    @empty
                        <p class="rounded-xl bg-epf-lavender px-5 py-6 text-center text-epf-muted">Aucun document transmis.</p>
                    @endforelse
                </div>
            </details>

            <details class="overflow-hidden rounded-2xl border border-purple-100 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100 [&::-webkit-details-marker]:hidden">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Traçabilité</p>
                        <h2 class="mt-1 text-xl font-bold">Historique du dossier</h2>
                    </div>
                    <span class="rounded-full bg-epf-lavender px-3 py-1 text-xs font-bold">{{ $candidature->historiques->count() }} action(s)</span>
                </summary>
                <div class="max-h-96 space-y-3 overflow-y-auto border-t border-purple-100 p-4 sm:p-5">
                    @forelse ($candidature->historiques as $historique)
                        <article class="rounded-xl border border-purple-100 bg-epf-lavender p-4">
                            <div class="flex flex-wrap justify-between gap-2">
                                <p class="font-bold">{{ \App\Enums\CandidatureStatut::tryFrom($historique->nouveau_statut)?->libelle() ?? $historique->nouveau_statut }}</p>
                                <time class="text-xs text-epf-muted">{{ $historique->created_at->format('d/m/Y H:i') }}</time>
                            </div>
                            @if ($historique->commentaire)
                                <p class="mt-2 text-sm text-epf-muted">{{ $historique->commentaire }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-epf-muted">Aucun événement enregistré.</p>
                    @endforelse
                </div>
            </details>
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Candidat</p>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-bold">Email</dt>
                        <dd class="mt-1 break-all text-epf-muted">{{ $candidature->candidat->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold">Téléphone</dt>
                        <dd class="mt-1 text-epf-muted">{{ $candidature->candidat->telephone ?: 'Non renseigné' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold">Pays</dt>
                        <dd class="mt-1 text-epf-muted">{{ $candidature->candidat->pays ?: 'Non renseigné' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Traitement</p>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-bold">Soumise le</dt>
                        <dd class="mt-1 text-epf-muted">{{ $candidature->submitted_at?->format('d/m/Y H:i') ?? 'Non renseignée' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold">Contrôlée par</dt>
                        <dd class="mt-1 text-epf-muted">{{ $candidature->agentAdmission?->name ?? 'Service d’admission' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-purple-200 bg-purple-50 p-5">
                <p class="font-bold">Consultation uniquement</p>
                <p class="mt-2 text-sm leading-6 text-epf-muted">
                    La décision, la demande de complément et la réorientation seront ajoutées lors de la prochaine fonctionnalité.
                </p>
            </section>
        </aside>
    </div>
</x-jury-layout>
