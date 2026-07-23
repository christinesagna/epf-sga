<x-jury-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Étude des candidatures</p>
        <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Dossiers transmis au jury</h1>
        <p class="mt-4 max-w-3xl leading-7 text-purple-100">
            Seuls les dossiers ayant terminé le contrôle du service d’admission sont visibles ici.
        </p>
    </section>

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
            <p class="font-bold">Les filtres ne sont pas valides.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mt-8">
        <form method="GET" action="{{ route('jury.candidatures.index') }}" class="grid gap-4 rounded-2xl border border-purple-100 bg-white p-5 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-input-label for="recherche" value="Rechercher un candidat" />
                <x-text-input id="recherche" name="recherche" type="search" :value="$filtres['recherche'] ?? ''" placeholder="Nom, prénom, email ou référence du dossier" />
            </div>

            <div>
                <x-input-label for="statut" value="Statut" />
                <select id="statut" name="statut" class="mt-2 block min-h-12 w-full rounded-xl border-gray-300 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-4 focus:ring-purple-100">
                    <option value="">Tous les statuts du jury</option>
                    @foreach ($statuts as $statut)
                        <option value="{{ $statut->value }}" @selected(($filtres['statut'] ?? '') === $statut->value)>{{ $statut->libelle() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="programme_id" value="Programme" />
                <select id="programme_id" name="programme_id" class="mt-2 block min-h-12 w-full rounded-xl border-gray-300 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-4 focus:ring-purple-100">
                    <option value="">Tous les programmes</option>
                    @foreach ($programmes as $programme)
                        <option value="{{ $programme->id }}" @selected((string) ($filtres['programme_id'] ?? '') === (string) $programme->id)>{{ $programme->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="programme_niveau_id" value="Niveau du programme" />
                <select id="programme_niveau_id" name="programme_niveau_id" class="mt-2 block min-h-12 w-full rounded-xl border-gray-300 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-4 focus:ring-purple-100">
                    <option value="">Tous les niveaux</option>
                    @foreach ($niveauxProgrammes as $programmeNiveau)
                        <option value="{{ $programmeNiveau->id }}" @selected((string) ($filtres['programme_niveau_id'] ?? '') === (string) $programmeNiveau->id)>
                            {{ $programmeNiveau->programme->nom }} — {{ $programmeNiveau->niveau->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="date_debut" value="Du" />
                    <x-text-input id="date_debut" name="date_debut" type="date" :value="$filtres['date_debut'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="date_fin" value="Au" />
                    <x-text-input id="date_fin" name="date_fin" type="date" :value="$filtres['date_fin'] ?? ''" />
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-3 lg:col-span-3">
                <button type="submit" class="rounded-xl bg-epf-purple px-5 py-3 font-bold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">Filtrer</button>
                <a href="{{ route('jury.candidatures.index') }}" class="rounded-xl border border-purple-200 bg-white px-5 py-3 font-semibold text-epf-purple">Réinitialiser</a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-2xl border border-purple-100 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-purple-100 px-5 py-4">
            <h2 class="text-xl font-bold">Résultats</h2>
            <span class="rounded-full bg-epf-lavender px-3 py-1 text-sm font-bold">{{ $candidatures->total() }} dossier(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Candidat</th>
                        <th class="px-5 py-4">Programme / niveau</th>
                        <th class="px-5 py-4">Soumission</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($candidatures as $candidature)
                        <tr>
                            <td class="px-5 py-5">
                                <p class="font-bold">{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}</p>
                                <p class="mt-1 text-xs text-epf-muted">{{ $candidature->candidat->email }}</p>
                            </td>
                            <td class="px-5 py-5 text-sm">
                                <p class="font-semibold">{{ $candidature->programme?->nom ?? 'Programme indisponible' }}</p>
                                <p class="mt-1 text-xs text-epf-muted">{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}</p>
                            </td>
                            <td class="px-5 py-5 text-sm text-epf-muted">{{ $candidature->submitted_at?->format('d/m/Y H:i') ?? 'Non renseignée' }}</td>
                            <td class="px-5 py-5 text-sm font-semibold">{{ $candidature->statut->libelle() }}</td>
                            <td class="px-5 py-5 text-right">
                                <a href="{{ route('jury.candidatures.show', $candidature) }}" class="inline-flex rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">
                                    Consulter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-epf-muted">Aucun dossier ne correspond aux filtres.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($candidatures->hasPages())
            <div class="border-t border-purple-100 px-5 py-4">{{ $candidatures->links() }}</div>
        @endif
    </section>
</x-jury-layout>
