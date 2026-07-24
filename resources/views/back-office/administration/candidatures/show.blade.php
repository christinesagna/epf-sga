<x-administration-layout>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('administration.candidatures.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-epf-purple hover:text-epf-red">
            <span aria-hidden="true">←</span> Retour aux candidatures
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-purple-100 px-4 py-2 text-sm font-bold text-epf-purple">Consultation en lecture seule</span>
            <span class="rounded-full bg-white px-4 py-2 text-sm font-bold text-epf-purple shadow-sm">{{ $candidature->statut->libelle() }}</span>
        </div>
    </div>

    <section class="rounded-3xl bg-epf-purple px-6 py-7 text-white shadow-[0_20px_60px_rgba(38,0,82,0.14)] sm:px-8">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">{{ $candidature->code_suivi ?: 'Dossier #'.$candidature->id }}</p>
        <h1 class="mt-2 text-2xl font-bold sm:text-3xl">{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}</h1>
        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-purple-100">
            <span>{{ $candidature->programme?->nom ?? 'Programme indisponible' }}</span>
            <span>{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}</span>
            <span>{{ $candidature->documents->count() }} document(s)</span>
        </div>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-2">
        <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Candidat</p>
            <h2 class="mt-2 text-xl font-bold">Informations personnelles</h2>
            <dl class="mt-5 grid gap-x-5 gap-y-4 sm:grid-cols-2">
                @foreach ([
                    ['label' => 'Nom', 'valeur' => $candidature->candidat->nom],
                    ['label' => 'Prénom', 'valeur' => $candidature->candidat->prenom],
                    ['label' => 'Email', 'valeur' => $candidature->candidat->email],
                    ['label' => 'Téléphone', 'valeur' => $candidature->candidat->telephone],
                    ['label' => 'Date de naissance', 'valeur' => $candidature->candidat->date_naissance?->format('d/m/Y')],
                    ['label' => 'Pays', 'valeur' => $candidature->candidat->pays],
                    ['label' => 'Sexe', 'valeur' => $candidature->candidat->sexe],
                    ['label' => 'Adresse', 'valeur' => $candidature->candidat->adresse],
                ] as $information)
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">{{ $information['label'] }}</dt><dd class="mt-1 break-words text-sm font-semibold">{{ $information['valeur'] ?: 'Non renseigné' }}</dd></div>
                @endforeach
            </dl>
        </article>

        <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Formation</p>
            <h2 class="mt-2 text-xl font-bold">Parcours demandé</h2>
            <dl class="mt-5 grid gap-x-5 gap-y-4 sm:grid-cols-2">
                @foreach ([
                    ['label' => 'Programme', 'valeur' => $candidature->programme?->nom],
                    ['label' => 'Niveau', 'valeur' => $candidature->programmeNiveau?->niveau?->libelle],
                    ['label' => 'Dernière formation', 'valeur' => $candidature->derniere_formation],
                    ['label' => 'Établissement', 'valeur' => $candidature->etablissement_provenance],
                    ['label' => 'Soumission', 'valeur' => $candidature->submitted_at?->format('d/m/Y à H:i')],
                    ['label' => 'Agent d’admission', 'valeur' => $candidature->agentAdmission?->name ?? 'Non attribué'],
                    ['label' => 'Prise en charge', 'valeur' => $candidature->pris_en_charge_at?->format('d/m/Y à H:i')],
                ] as $information)
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">{{ $information['label'] }}</dt><dd class="mt-1 break-words text-sm font-semibold">{{ $information['valeur'] ?: 'Non renseigné' }}</dd></div>
                @endforeach
            </dl>
            @if ($candidature->motivation)
                <div class="mt-5 border-t border-purple-100 pt-5"><p class="text-xs font-bold uppercase tracking-wide text-epf-muted">Motivation</p><p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $candidature->motivation }}</p></div>
            @endif
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Pièces justificatives</p><h2 class="mt-2 text-xl font-bold">Documents transmis</h2></div>
            <p class="text-sm text-epf-muted">{{ $candidature->documents->count() }} document(s)</p>
        </div>
        <div class="mt-5 overflow-x-auto rounded-xl border border-purple-100">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted"><tr><th class="px-4 py-3">Document</th><th class="px-4 py-3">Fichier</th><th class="px-4 py-3">Validation</th><th class="px-4 py-3 text-right">Consultation</th></tr></thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($candidature->documents as $document)
                        <tr>
                            <td class="px-4 py-4 font-semibold">{{ $document->typeDocument->libelle }}</td>
                            <td class="px-4 py-4 text-sm text-epf-muted">{{ $document->original_name }} · {{ number_format($document->size / 1024, 1, ',', ' ') }} Ko</td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-epf-purple">{{ $document->statut_validation?->libelle() ?? 'Non contrôlé' }}</span>@if ($document->commentaire_validation)<p class="mt-2 max-w-sm text-xs text-epf-muted">{{ $document->commentaire_validation }}</p>@endif</td>
                            <td class="px-4 py-4 text-right"><a href="{{ route('administration.candidature-documents.show', $document) }}" target="_blank" rel="noopener" class="inline-flex rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-epf-muted">Aucun document transmis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-6 rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Traçabilité</p>
        <h2 class="mt-2 text-xl font-bold">Historique du dossier</h2>
        <div class="mt-5 space-y-3">
            @forelse ($candidature->historiques as $historique)
                <article class="flex flex-col gap-3 rounded-xl border border-purple-100 bg-epf-lavender p-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="font-bold">
                            @if (($historique->metadata['action'] ?? null) === 'reorientation')
                                Réorientation du dossier
                            @else
                                {{ \App\Enums\CandidatureStatut::tryFrom($historique->nouveau_statut)?->libelle() ?? $historique->nouveau_statut }}
                            @endif
                        </p>
                        @if (($historique->metadata['action'] ?? null) === 'reorientation')
                            <p class="mt-1 text-sm font-semibold">{{ $programmesHistorique->get($historique->metadata['ancien_programme_id'] ?? null, 'Programme précédent') }} → {{ $programmesHistorique->get($historique->metadata['nouveau_programme_id'] ?? null, 'Nouveau programme') }}</p>
                        @endif
                        @if ($historique->commentaire)<p class="mt-1 text-sm text-epf-muted">{{ $historique->commentaire }}</p>@endif
                    </div>
                    <div class="shrink-0 text-xs text-epf-muted sm:text-right">
                        <p>{{ $acteursHistorique->get($historique->acteur_id, ucfirst($historique->acteur_type)) }}</p>
                        <time class="mt-1 block">{{ $historique->created_at->format('d/m/Y H:i') }}</time>
                    </div>
                </article>
            @empty
                <p class="rounded-xl bg-epf-lavender px-5 py-6 text-epf-muted">Aucun événement enregistré.</p>
            @endforelse
        </div>
    </section>
</x-administration-layout>
