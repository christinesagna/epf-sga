<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Catalogue des formations</p>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Gestion des programmes</h1>
                <p class="mt-4 max-w-3xl leading-7 text-purple-100">
                    Configurez les formations présentées aux candidats, leurs périodes d’ouverture et les niveaux auxquels ils peuvent postuler.
                </p>
            </div>
            <a href="{{ route('administration.programmes.create') }}" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200">
                Créer un programme
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
            <p class="font-bold">L’action n’a pas pu être enregistrée.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mt-8" aria-labelledby="programmes-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Formations</p>
                <h2 id="programmes-title" class="mt-2 text-2xl font-bold">Programmes enregistrés</h2>
            </div>
            <p class="text-sm font-semibold text-epf-muted">{{ $programmes->total() }} programme(s)</p>
        </div>

        <form method="GET" action="{{ route('administration.programmes.index') }}" class="mt-5 grid gap-4 rounded-2xl border border-purple-100 bg-white p-5 md:grid-cols-[1.4fr_1fr_1fr_auto] md:items-end">
            <div>
                <x-input-label for="recherche" value="Rechercher" />
                <x-text-input id="recherche" name="recherche" type="search" :value="$filtres['recherche'] ?? ''" placeholder="Nom du programme" />
            </div>

            <div>
                <x-input-label for="cycle" value="Cycle du programme" />
                <select id="cycle" name="cycle" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    <option value="">Tous les cycles</option>
                    @foreach ($cycles as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected(($filtres['cycle'] ?? '') === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="etat" value="État" />
                <select id="etat" name="etat" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    <option value="">Tous les états</option>
                    <option value="actif" @selected(($filtres['etat'] ?? '') === 'actif')>Actif</option>
                    <option value="inactif" @selected(($filtres['etat'] ?? '') === 'inactif')>Inactif</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-epf-purple px-5 py-3 font-semibold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">Filtrer</button>
                <a href="{{ route('administration.programmes.index') }}" class="rounded-xl border border-purple-200 px-5 py-3 font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">Effacer</a>
            </div>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-purple-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Programme</th>
                        <th class="px-5 py-4">Cycle</th>
                        <th class="px-5 py-4">Niveaux</th>
                        <th class="px-5 py-4">Période</th>
                        <th class="px-5 py-4">État</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($programmes as $programme)
                        <tr class="align-top">
                            <td class="px-5 py-5">
                                <p class="font-bold text-epf-purple">{{ $programme->nom }}</p>
                                <p class="mt-1 text-sm text-epf-muted">Capacité : {{ $programme->capacite_accueil }}</p>
                            </td>
                            <td class="px-5 py-5 text-sm text-epf-muted">
                                {{ $cycles[$programme->niveau] ?? $programme->niveau }}
                            </td>
                            <td class="px-5 py-5">
                                <p class="font-semibold">{{ $programme->niveaux_actifs_count }} actif(s)</p>
                                <p class="mt-1 text-xs text-epf-muted">{{ $programme->niveaux_count }} associé(s)</p>
                            </td>
                            <td class="px-5 py-5 text-sm text-epf-muted">
                                @if ($programme->date_ouverture || $programme->date_fermeture)
                                    <p>Du {{ $programme->date_ouverture?->format('d/m/Y') ?? '—' }}</p>
                                    <p>au {{ $programme->date_fermeture?->format('d/m/Y') ?? '—' }}</p>
                                @else
                                    Non définie
                                @endif
                            </td>
                            <td class="px-5 py-5">
                                @if ($programme->actif)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">Actif</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">Inactif</span>
                                @endif
                            </td>
                            <td class="px-5 py-5">
                                <div class="flex min-w-52 justify-end gap-2">
                                    <a href="{{ route('administration.programmes.edit', $programme) }}" class="rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple hover:border-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">
                                        Modifier
                                    </a>
                                    <form method="POST" action="{{ route('administration.programmes.etat', $programme) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="actif" value="{{ $programme->actif ? 0 : 1 }}">
                                        <button type="submit" class="rounded-xl border px-4 py-2 text-sm font-semibold focus:outline-none focus:ring-4 {{ $programme->actif ? 'border-red-200 text-epf-red hover:bg-red-50 focus:ring-red-100' : 'border-green-200 text-green-800 hover:bg-green-50 focus:ring-green-100' }}">
                                            {{ $programme->actif ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-epf-muted">Aucun programme ne correspond aux filtres.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $programmes->links() }}
        </div>
    </section>
</x-administration-layout>
