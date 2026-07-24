<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Pièces justificatives</p>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Gestion des documents</h1>
                <p class="mt-4 max-w-3xl leading-7 text-purple-100">Définissez les pièces acceptées, leurs formats et leur taille. Leur utilisation se configure ensuite pour chaque niveau.</p>
            </div>
            <a href="{{ route('administration.documents.create') }}" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200">Créer un type de document</a>
        </div>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
            <p class="font-bold">L’action n’a pas pu être enregistrée.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="mt-8" aria-labelledby="documents-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Catalogue commun</p>
                <h2 id="documents-title" class="mt-2 text-2xl font-bold">Types de documents enregistrés</h2>
            </div>
            <p class="text-sm font-semibold text-epf-muted">{{ $typesDocuments->total() }} type(s)</p>
        </div>

        <form method="GET" action="{{ route('administration.documents.index') }}" class="mt-5 grid gap-4 rounded-2xl border border-purple-100 bg-white p-5 md:grid-cols-[1.5fr_1fr_auto] md:items-end">
            <div>
                <x-input-label for="recherche" value="Rechercher" />
                <x-text-input id="recherche" name="recherche" type="search" :value="$filtres['recherche'] ?? ''" placeholder="Libellé ou code" />
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
                <a href="{{ route('administration.documents.index') }}" class="rounded-xl border border-purple-200 px-5 py-3 font-semibold text-epf-purple">Effacer</a>
            </div>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-purple-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Document</th>
                        <th class="px-5 py-4">Formats</th>
                        <th class="px-5 py-4">Taille</th>
                        <th class="px-5 py-4">Niveaux associés</th>
                        <th class="px-5 py-4">État</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($typesDocuments as $typeDocument)
                        <tr class="align-top">
                            <td class="px-5 py-5"><p class="font-bold">{{ $typeDocument->libelle }}</p><p class="mt-1 font-mono text-xs text-epf-muted">{{ $typeDocument->code }}</p></td>
                            <td class="px-5 py-5 text-sm font-semibold">{{ collect($typeDocument->extensions_autorisees)->map(fn ($extension) => strtoupper($extension))->join(', ') }}</td>
                            <td class="px-5 py-5 text-sm text-epf-muted">{{ $typeDocument->taille_max_mb }} Mo</td>
                            <td class="px-5 py-5"><p class="font-semibold">{{ $typeDocument->niveaux_programmes_count }}</p><p class="mt-1 text-xs text-epf-muted">association(s)</p></td>
                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $typeDocument->actif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $typeDocument->actif ? 'Actif' : 'Inactif' }}</span>
                            </td>
                            <td class="px-5 py-5">
                                <div class="flex min-w-52 justify-end gap-2">
                                    <a href="{{ route('administration.documents.edit', $typeDocument) }}" class="rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple">Modifier</a>
                                    <form method="POST" action="{{ route('administration.documents.etat', $typeDocument) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="actif" value="{{ $typeDocument->actif ? 0 : 1 }}">
                                        <button type="submit" class="rounded-xl border px-4 py-2 text-sm font-semibold {{ $typeDocument->actif ? 'border-red-200 text-epf-red' : 'border-green-200 text-green-800' }}">{{ $typeDocument->actif ? 'Désactiver' : 'Activer' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-epf-muted">Aucun type de document ne correspond aux filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $typesDocuments->links() }}</div>
    </section>
</x-administration-layout>
