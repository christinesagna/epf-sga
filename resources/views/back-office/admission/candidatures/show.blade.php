<x-admission-layout>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admission.candidatures.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-epf-purple hover:text-epf-red focus:outline-none focus:ring-4 focus:ring-purple-100">
            <span aria-hidden="true">←</span>
            Retour aux candidatures
        </a>
        <span class="rounded-full bg-white px-4 py-2 text-sm font-bold text-epf-purple shadow-sm">
            {{ $candidature->statut->libelle() }}
        </span>
    </div>

    <section class="rounded-3xl bg-epf-purple px-6 py-5 text-white shadow-[0_20px_60px_rgba(38,0,82,0.14)] sm:px-8">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">
                    {{ $candidature->code_suivi ?: 'Dossier #'.$candidature->id }}
                </p>
                <h1 class="mt-2 truncate text-2xl font-bold sm:text-3xl">
                    {{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}
                </h1>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-purple-100">
                    <span>{{ $candidature->programme?->nom ?? 'Programme indisponible' }}</span>
                    <span>{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}</span>
                    <span>{{ $candidature->documents->count() }} document(s)</span>
                </div>
            </div>

            @can('prendreEnCharge', $candidature)
                <form method="POST" action="{{ route('admission.candidatures.prise-en-charge', $candidature) }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-epf-red px-6 py-3 font-bold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 xl:w-auto">
                        Prendre en charge
                    </button>
                </form>
            @endcan
        </div>
    </section>

    @if (session('status'))
        <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-semibold text-green-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-900" role="status">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm text-red-800" role="alert">
            <p class="font-bold">L’action n’a pas pu être réalisée.</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-5 space-y-3">
        <details name="sections-dossier" class="overflow-hidden rounded-2xl border border-purple-100 bg-white shadow-sm" @if ($candidature->statut !== \App\Enums\CandidatureStatut::EN_TRAITEMENT_ADMISSION) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100 [&::-webkit-details-marker]:hidden">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Vue d’ensemble</p>
                    <h2 class="mt-1 text-lg font-bold">Candidat et formation</h2>
                </div>
                <span class="rounded-lg bg-epf-lavender px-3 py-2 text-sm font-bold">Afficher</span>
            </summary>

            <div class="grid gap-6 border-t border-purple-100 p-5 xl:grid-cols-2">
                <section aria-labelledby="candidat-title">
                    <h3 id="candidat-title" class="font-bold">Informations du candidat</h3>
                    <dl class="mt-4 grid gap-x-5 gap-y-4 sm:grid-cols-2">
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
                            <div class="min-w-0">
                                <dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">{{ $information['label'] }}</dt>
                                <dd class="mt-1 break-words text-sm font-semibold">{{ $information['valeur'] ?: 'Non renseigné' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section aria-labelledby="formation-title">
                    <h3 id="formation-title" class="font-bold">Formation demandée</h3>
                    <dl class="mt-4 grid gap-x-5 gap-y-4 sm:grid-cols-2">
                        @foreach ([
                            ['label' => 'Programme', 'valeur' => $candidature->programme?->nom ?? 'Programme indisponible'],
                            ['label' => 'Niveau', 'valeur' => $candidature->programmeNiveau?->niveau?->libelle],
                            ['label' => 'Dernière formation', 'valeur' => $candidature->derniere_formation],
                            ['label' => 'Établissement', 'valeur' => $candidature->etablissement_provenance],
                            ['label' => 'Soumission', 'valeur' => $candidature->submitted_at?->format('d/m/Y à H:i')],
                            ['label' => 'Agent d’admission', 'valeur' => $candidature->agentAdmission?->name ?? 'Dossier non attribué'],
                        ] as $information)
                            <div class="min-w-0">
                                <dt class="text-xs font-bold uppercase tracking-wide text-epf-muted">{{ $information['label'] }}</dt>
                                <dd class="mt-1 break-words text-sm font-semibold">{{ $information['valeur'] ?: 'Non renseigné' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            </div>
        </details>

        <details name="sections-dossier" class="overflow-hidden rounded-2xl border border-purple-100 bg-white shadow-sm" @if ($candidature->statut === \App\Enums\CandidatureStatut::EN_TRAITEMENT_ADMISSION) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100 [&::-webkit-details-marker]:hidden">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Pièces justificatives</p>
                    <h2 class="mt-1 text-lg font-bold">Documents et contrôle admission</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-epf-lavender px-3 py-1 text-xs font-bold">
                        {{ $candidature->documents->where('statut_validation', \App\Enums\DocumentStatutValidation::VALIDE)->count() }}/{{ $candidature->documents->count() }} validé(s)
                    </span>
                    <span class="rounded-lg bg-epf-lavender px-3 py-2 text-sm font-bold">Afficher</span>
                </div>
            </summary>

            <div class="border-t border-purple-100 p-4 sm:p-5">
                <div class="space-y-3">
                    @forelse ($candidature->documents as $document)
                        <article class="rounded-xl border border-purple-100 bg-epf-lavender/50 p-4">
                            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_auto_minmax(26rem,1.5fr)] xl:items-center">
                                <div class="min-w-0">
                                    <p class="truncate font-bold">{{ $document->typeDocument->libelle }}</p>
                                    <p class="mt-1 truncate text-sm text-epf-muted">
                                        {{ $document->original_name }} · {{ number_format($document->size / 1024, 1, ',', ' ') }} Ko
                                    </p>
                                    @if ($documentsObligatoires->contains('id', $document->type_document_id))
                                        <span class="mt-1 inline-flex rounded-full bg-red-50 px-2 py-0.5 text-xs font-bold text-epf-red">Obligatoire</span>
                                    @endif
                                    <span @class([
                                        'mt-1 inline-flex w-fit rounded-full px-2 py-0.5 text-xs font-bold',
                                        'bg-green-100 text-green-800' => $document->statut_validation === \App\Enums\DocumentStatutValidation::VALIDE,
                                        'bg-red-100 text-red-800' => $document->statut_validation === \App\Enums\DocumentStatutValidation::REJETE,
                                        'bg-amber-100 text-amber-900' => $document->statut_validation === \App\Enums\DocumentStatutValidation::EN_ATTENTE,
                                    ])>
                                        {{ $document->statut_validation->libelle() }}
                                    </span>
                                </div>

                                <a href="{{ route('admission.documents.show', $document) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-purple-200 bg-white px-4 py-2 text-sm font-semibold text-epf-purple hover:border-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">
                                    Ouvrir
                                </a>

                                @can('controlerDocuments', $candidature)
                                    <form method="POST" action="{{ route('admission.documents.update', $document) }}" class="grid gap-3 sm:grid-cols-[minmax(10rem,1fr)_auto] sm:items-end" data-document-control>
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="text-xs font-bold uppercase tracking-wide text-epf-muted" for="statut_validation_{{ $document->id }}">
                                                Décision
                                            </label>
                                            <select id="statut_validation_{{ $document->id }}" name="statut_validation" class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 text-sm text-epf-purple shadow-sm focus:border-epf-purple focus:ring-4 focus:ring-purple-100" data-document-decision>
                                                <option value="valide" @selected($document->statut_validation === \App\Enums\DocumentStatutValidation::VALIDE)>Valider</option>
                                                <option value="rejete" @selected($document->statut_validation === \App\Enums\DocumentStatutValidation::REJETE)>Rejeter</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="min-h-11 rounded-xl bg-epf-purple px-4 py-2 text-sm font-bold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">
                                            Enregistrer
                                        </button>
                                        <div @class([
                                            'sm:col-span-2',
                                            'hidden' => $document->statut_validation !== \App\Enums\DocumentStatutValidation::REJETE,
                                        ]) data-rejection-reason>
                                            <label class="text-xs font-bold uppercase tracking-wide text-epf-muted" for="commentaire_validation_{{ $document->id }}">
                                                Motif du rejet
                                            </label>
                                            <input id="commentaire_validation_{{ $document->id }}" name="commentaire_validation" maxlength="1000" value="{{ $document->commentaire_validation }}" placeholder="Indiquez pourquoi ce document est refusé" class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 text-sm text-epf-purple shadow-sm focus:border-epf-purple focus:ring-4 focus:ring-purple-100" data-rejection-input @required($document->statut_validation === \App\Enums\DocumentStatutValidation::REJETE)>
                                        </div>
                                    </form>
                                @else
                                    @if ($document->commentaire_validation)
                                        <p class="text-sm text-epf-muted">{{ $document->commentaire_validation }}</p>
                                    @endif
                                @endcan
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-epf-lavender px-5 py-6 text-center text-epf-muted">Aucun document n’a été transmis.</p>
                    @endforelse
                </div>

                @can('demanderComplement', $candidature)
                    @if ($typesDocumentsACompleter->isNotEmpty())
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="font-bold text-amber-950">Demander des documents complémentaires</p>
                            <p class="mt-1 text-sm text-amber-900">
                                Documents concernés :
                                {{ $typesDocumentsACompleter->pluck('libelle')->implode(', ') }}.
                            </p>
                            <form method="POST" action="{{ route('admission.candidatures.demande-complement', $candidature) }}" class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                                @csrf
                                <div>
                                    <label for="motif_complement" class="text-xs font-bold uppercase tracking-wide text-amber-950">
                                        Message destiné au candidat
                                    </label>
                                    <textarea id="motif_complement" name="motif_complement" rows="2" maxlength="2000" required placeholder="Précisez les corrections ou documents attendus." class="mt-1 block w-full rounded-xl border-amber-300 text-sm text-epf-purple shadow-sm focus:border-epf-purple focus:ring-4 focus:ring-purple-100">{{ old('motif_complement') }}</textarea>
                                </div>
                                <button type="submit" class="rounded-xl bg-epf-red px-5 py-3 text-sm font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                                    Envoyer la demande
                                </button>
                            </form>
                        </div>
                    @elseif (! $documentsObligatoiresValides)
                        <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                            Contrôlez les documents en attente avant de demander un complément.
                        </p>
                    @endif
                @endcan

                @can('transmettreAuJury', $candidature)
                    <div class="mt-4 flex flex-col gap-4 rounded-xl border border-purple-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-bold">Transmission au jury</p>
                            <p class="mt-1 text-sm text-epf-muted">
                                {{ $documentsObligatoiresValides
                                    ? 'Tous les documents obligatoires sont validés.'
                                    : 'Chaque document obligatoire doit être présent et validé.' }}
                            </p>
                        </div>
                        @if ($documentsObligatoiresValides)
                            <form method="POST" action="{{ route('admission.candidatures.transmission-jury', $candidature) }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-epf-red px-5 py-3 text-sm font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100 sm:w-auto">
                                    Transmettre au jury
                                </button>
                            </form>
                        @else
                            <span class="shrink-0 rounded-full bg-amber-100 px-4 py-2 text-xs font-bold text-amber-900">Transmission bloquée</span>
                        @endif
                    </div>
                @endcan
            </div>
        </details>

        <details name="sections-dossier" class="overflow-hidden rounded-2xl border border-purple-100 bg-white shadow-sm">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100 [&::-webkit-details-marker]:hidden">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-epf-red">Traçabilité</p>
                    <h2 class="mt-1 text-lg font-bold">Historique du dossier</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-epf-lavender px-3 py-1 text-xs font-bold">{{ $candidature->historiques->count() }} action(s)</span>
                    <span class="rounded-lg bg-epf-lavender px-3 py-2 text-sm font-bold">Afficher</span>
                </div>
            </summary>

            <div class="max-h-96 space-y-3 overflow-y-auto border-t border-purple-100 p-4 sm:p-5">
                @forelse ($candidature->historiques as $historique)
                    <article class="flex flex-col gap-2 rounded-xl border border-purple-100 bg-epf-lavender p-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-bold">
                                @if (($historique->metadata['action'] ?? null) === 'reorientation')
                                    Réorientation du dossier
                                @else
                                    {{ \App\Enums\CandidatureStatut::tryFrom($historique->nouveau_statut)?->libelle() ?? $historique->nouveau_statut }}
                                @endif
                            </p>
                            @if (($historique->metadata['action'] ?? null) === 'reorientation')
                                <p class="mt-1 text-sm font-semibold text-epf-purple">
                                    {{ $programmesHistorique->get($historique->metadata['ancien_programme_id'] ?? null, 'Programme précédent') }}
                                    <span aria-hidden="true">→</span>
                                    {{ $programmesHistorique->get($historique->metadata['nouveau_programme_id'] ?? null, 'Nouveau programme') }}
                                </p>
                            @endif
                            @if ($historique->commentaire)
                                <p class="mt-1 text-sm text-epf-muted">{{ $historique->commentaire }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 text-left text-xs text-epf-muted sm:text-right">
                            <p>{{ $historique->acteur_type }}{{ $historique->acteur_id ? ' #'.$historique->acteur_id : '' }}</p>
                            <time class="mt-1 block">{{ $historique->created_at->format('d/m/Y H:i') }}</time>
                        </div>
                    </article>
                @empty
                    <p class="rounded-xl bg-epf-lavender px-5 py-6 text-epf-muted">Aucun événement n’est encore enregistré.</p>
                @endforelse
            </div>
        </details>
    </div>
</x-admission-layout>
