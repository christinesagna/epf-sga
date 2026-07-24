<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <div class="flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Programme</p>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $programme->nom }}</h1>
                <p class="mt-4 text-purple-100">Modifiez les informations générales puis configurez les niveaux proposés.</p>
            </div>
            <span class="rounded-full px-4 py-2 text-sm font-bold {{ $programme->actif ? 'bg-green-100 text-green-800' : 'bg-white/10 text-white' }}">
                {{ $programme->actif ? 'Actif' : 'Inactif' }}
            </span>
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

    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="informations-title">
        <h2 id="informations-title" class="text-2xl font-bold">Informations générales</h2>
        <p class="mt-2 text-sm text-epf-muted">Le renommage ne modifie pas le slug afin de préserver les liens publics existants.</p>

        <form method="POST" action="{{ route('administration.programmes.update', $programme) }}" class="mt-7">
            @include('back-office.administration.programmes._form')
        </form>
    </section>

    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="niveaux-title">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Parcours de candidature</p>
            <h2 id="niveaux-title" class="mt-2 text-2xl font-bold">Niveaux du programme</h2>
            <p class="mt-3 max-w-4xl leading-7 text-epf-muted">
                Un niveau du catalogue peut être réutilisé par plusieurs programmes. Son ordre et son état sont en revanche propres à ce programme.
            </p>
        </div>

        <div class="mt-7 overflow-x-auto rounded-2xl border border-purple-100">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Niveau</th>
                        <th class="px-5 py-4">Code</th>
                        <th class="px-5 py-4">Ordre et état</th>
                        <th class="px-5 py-4 text-right">Documents</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($programme->niveaux as $programmeNiveau)
                        <tr>
                            <td class="px-5 py-5 font-bold">{{ $programmeNiveau->niveau->libelle }}</td>
                            <td class="px-5 py-5 text-sm text-epf-muted">{{ $programmeNiveau->niveau->code }}</td>
                            <td class="px-5 py-5">
                                <form method="POST" action="{{ route('administration.programme-niveaux.update', $programmeNiveau) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="ordre-{{ $programmeNiveau->id }}" class="block text-xs font-bold text-epf-muted">Ordre</label>
                                        <input id="ordre-{{ $programmeNiveau->id }}" name="ordre" type="number" min="1" max="999" value="{{ $programmeNiveau->ordre }}" class="mt-1 w-24 rounded-xl border-purple-200 text-sm focus:border-epf-purple focus:ring-epf-purple">
                                    </div>
                                    <div>
                                        <label for="actif-{{ $programmeNiveau->id }}" class="block text-xs font-bold text-epf-muted">État</label>
                                        <select id="actif-{{ $programmeNiveau->id }}" name="actif" class="mt-1 rounded-xl border-purple-200 text-sm focus:border-epf-purple focus:ring-epf-purple">
                                            <option value="1" @selected($programmeNiveau->actif)>Actif</option>
                                            <option value="0" @selected(! $programmeNiveau->actif)>Inactif</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="rounded-xl border border-purple-200 px-4 py-2.5 text-sm font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">Enregistrer</button>
                                </form>
                            </td>
                            <td class="px-5 py-5 text-right">
                                <a href="{{ route('administration.programme-niveaux.documents.edit', $programmeNiveau) }}" class="inline-flex rounded-xl bg-epf-purple px-4 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">
                                    Configurer
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-epf-muted">Aucun niveau n’est encore associé à ce programme.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-7 grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('administration.programmes.niveaux.store', $programme) }}" class="rounded-2xl bg-epf-lavender p-5">
                @csrf
                <h3 class="font-bold">Associer un niveau existant</h3>
                <p class="mt-2 text-sm text-epf-muted">Réutilise un niveau déjà présent dans le catalogue commun.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-[1fr_7rem_auto] sm:items-end">
                    <div>
                        <x-input-label for="niveau_id" value="Niveau" />
                        <select id="niveau_id" name="niveau_id" required class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple focus:border-epf-purple focus:ring-epf-purple">
                            <option value="">Choisir</option>
                            @foreach ($niveauxDisponibles as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="ordre_existant" value="Ordre" />
                        <input id="ordre_existant" name="ordre" type="number" min="1" max="999" value="{{ max(1, $programme->niveaux->count() + 1) }}" required class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple focus:border-epf-purple focus:ring-epf-purple">
                    </div>
                    <button type="submit" @disabled($niveauxDisponibles->isEmpty()) class="rounded-xl bg-epf-purple px-5 py-3 font-semibold text-white focus:outline-none focus:ring-4 focus:ring-purple-200 disabled:cursor-not-allowed disabled:opacity-50">Associer</button>
                </div>
            </form>

            <form method="POST" action="{{ route('administration.programmes.niveaux.nouveau', $programme) }}" class="rounded-2xl bg-epf-lavender p-5">
                @csrf
                <h3 class="font-bold">Créer un nouveau niveau</h3>
                <p class="mt-2 text-sm text-epf-muted">Ajoute un niveau au catalogue commun et l’associe immédiatement.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-[1fr_7rem_auto] sm:items-end">
                    <div>
                        <x-input-label for="libelle" value="Libellé" />
                        <x-text-input id="libelle" name="libelle" type="text" placeholder="Ex. Bachelor 2" required />
                    </div>
                    <div>
                        <x-input-label for="ordre_nouveau" value="Ordre" />
                        <input id="ordre_nouveau" name="ordre" type="number" min="1" max="999" value="{{ max(1, $programme->niveaux->count() + 1) }}" required class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple focus:border-epf-purple focus:ring-epf-purple">
                    </div>
                    <button type="submit" class="rounded-xl bg-epf-purple px-5 py-3 font-semibold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">Créer</button>
                </div>
            </form>
        </div>
    </section>
</x-administration-layout>
