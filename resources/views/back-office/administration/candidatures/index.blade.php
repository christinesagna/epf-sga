<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Supervision des dossiers</p>
        <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Toutes les candidatures</h1>
        <p class="mt-4 max-w-3xl leading-7 text-purple-100">
            Consultez les dossiers soumis, leur affectation et leur progression dans les services Admission et Jury. Cet espace est en lecture seule.
        </p>
    </section>

    <section class="mt-8" aria-labelledby="liste-candidatures-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Dossiers</p>
                <h2 id="liste-candidatures-title" class="mt-2 text-2xl font-bold">Candidatures enregistrées</h2>
            </div>
            <p class="text-sm font-semibold text-epf-muted">{{ $candidatures->total() }} dossier(s)</p>
        </div>

        <form method="GET" action="{{ route('administration.candidatures.index') }}" class="mt-5 rounded-2xl border border-purple-100 bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <x-input-label for="recherche" value="Candidat ou code de suivi" />
                    <x-text-input id="recherche" name="recherche" type="search" :value="$filtres['recherche'] ?? ''" placeholder="Nom, email ou code" />
                </div>
                <div>
                    <x-input-label for="programme_id" value="Programme" />
                    <select id="programme_id" name="programme_id" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                        <option value="">Tous les programmes</option>
                        @foreach ($programmes as $programme)
                            <option value="{{ $programme->id }}" @selected((string) ($filtres['programme_id'] ?? '') === (string) $programme->id)>{{ $programme->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="programme_niveau_id" value="Niveau" />
                    <select id="programme_niveau_id" name="programme_niveau_id" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                        <option value="">Tous les niveaux</option>
                        @foreach ($niveauxProgrammes as $programmeNiveau)
                            <option value="{{ $programmeNiveau->id }}" @selected((string) ($filtres['programme_niveau_id'] ?? '') === (string) $programmeNiveau->id)>
                                {{ $programmeNiveau->programme->nom }} — {{ $programmeNiveau->niveau->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="statut" value="Statut" />
                    <select id="statut" name="statut" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuts as $statut)
                            <option value="{{ $statut->value }}" @selected(($filtres['statut'] ?? '') === $statut->value)>{{ $statut->libelle() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="agent_admission_id" value="Agent d’admission" />
                    <select id="agent_admission_id" name="agent_admission_id" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                        <option value="">Tous les agents</option>
                        @foreach ($agentsAdmission as $agent)
                            <option value="{{ $agent->id }}" @selected((string) ($filtres['agent_admission_id'] ?? '') === (string) $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="date_debut" value="Du" />
                        <input id="date_debut" name="date_debut" type="date" value="{{ $filtres['date_debut'] ?? '' }}" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    </div>
                    <div>
                        <x-input-label for="date_fin" value="Au" />
                        <input id="date_fin" name="date_fin" type="date" value="{{ $filtres['date_fin'] ?? '' }}" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    </div>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-epf-purple px-5 py-3 font-semibold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">Appliquer les filtres</button>
                <a href="{{ route('administration.candidatures.index') }}" class="rounded-xl border border-purple-200 px-5 py-3 font-semibold text-epf-purple">Effacer</a>
            </div>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-purple-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Candidat</th>
                        <th class="px-5 py-4">Programme et niveau</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Soumission</th>
                        <th class="px-5 py-4">Agent</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($candidatures as $candidature)
                        <tr class="align-top">
                            <td class="px-5 py-5">
                                <p class="font-bold">{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}</p>
                                <p class="mt-1 text-sm text-epf-muted">{{ $candidature->candidat->email }}</p>
                                <p class="mt-1 font-mono text-xs text-epf-muted">{{ $candidature->code_suivi ?: 'Dossier #'.$candidature->id }}</p>
                            </td>
                            <td class="px-5 py-5">
                                <p class="font-semibold">{{ $candidature->programme?->nom ?? 'Programme indisponible' }}</p>
                                <p class="mt-1 text-sm text-epf-muted">{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}</p>
                            </td>
                            <td class="px-5 py-5"><span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-epf-purple">{{ $candidature->statut->libelle() }}</span></td>
                            <td class="px-5 py-5 text-sm text-epf-muted">{{ $candidature->submitted_at?->format('d/m/Y H:i') ?? 'Non renseignée' }}</td>
                            <td class="px-5 py-5 text-sm">{{ $candidature->agentAdmission?->name ?? 'Non attribué' }}</td>
                            <td class="px-5 py-5 text-right"><a href="{{ route('administration.candidatures.show', $candidature) }}" class="inline-flex rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple hover:border-epf-purple">Consulter</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-epf-muted">Aucune candidature ne correspond aux filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $candidatures->links() }}</div>
    </section>
</x-administration-layout>
