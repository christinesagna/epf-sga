<x-admission-layout>
    <section class="overflow-hidden rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9 sm:py-10">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Service d’admission</p>
        <div class="mt-4 flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-3xl">
                <h1 class="text-3xl font-bold sm:text-4xl">Bonjour {{ auth()->user()->name }}</h1>
                <p class="mt-4 leading-7 text-purple-100">
                    Consultez les candidatures reçues et leurs pièces justificatives. Les actions de prise en charge seront ajoutées à l’étape suivante.
                </p>
            </div>
            <a href="{{ route('admission.candidatures.index') }}" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200">
                Voir les candidatures
            </a>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="indicateurs-admission-title">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Situation actuelle</p>
            <h2 id="indicateurs-admission-title" class="mt-2 text-2xl font-bold">Indicateurs des dossiers</h2>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['valeur' => $candidaturesRecues, 'libelle' => 'Dossiers reçus'],
                ['valeur' => $nouvellesCandidatures, 'libelle' => 'Nouvelles'],
                ['valeur' => $dossiersEnTraitement, 'libelle' => 'En traitement'],
                ['valeur' => $complementsEnAttente, 'libelle' => 'Compléments attendus'],
                ['valeur' => $mesDossiers, 'libelle' => 'Mes dossiers'],
            ] as $indicateur)
                <article class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-epf-muted">{{ $indicateur['libelle'] }}</p>
                    <p class="mt-3 text-4xl font-bold text-epf-purple">{{ $indicateur['valeur'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="recentes-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Dernières soumissions</p>
                <h2 id="recentes-title" class="mt-2 text-2xl font-bold">Candidatures récentes</h2>
            </div>
            <a href="{{ route('admission.candidatures.index') }}" class="font-semibold text-epf-red hover:underline">Consulter toute la liste</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-purple-100">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Candidat</th>
                        <th class="px-5 py-4">Programme</th>
                        <th class="px-5 py-4">Soumission</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4 text-right">Dossier</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($candidaturesRecentes as $candidature)
                        <tr>
                            <td class="px-5 py-5">
                                <p class="font-bold">{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}</p>
                                <p class="mt-1 text-xs text-epf-muted">{{ $candidature->code_suivi ?: 'Dossier #'.$candidature->id }}</p>
                            </td>
                            <td class="px-5 py-5 text-sm">
                                <p class="font-semibold">{{ $candidature->programme?->nom ?? 'Programme indisponible' }}</p>
                                <p class="mt-1 text-xs text-epf-muted">{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Niveau non renseigné' }}</p>
                            </td>
                            <td class="px-5 py-5 text-sm text-epf-muted">{{ $candidature->submitted_at?->format('d/m/Y H:i') ?? 'Non renseignée' }}</td>
                            <td class="px-5 py-5 text-sm font-semibold">{{ $candidature->statut->libelle() }}</td>
                            <td class="px-5 py-5 text-right">
                                <a href="{{ route('admission.candidatures.show', $candidature) }}" class="inline-flex rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">
                                    Ouvrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-epf-muted">Aucune candidature soumise pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admission-layout>
