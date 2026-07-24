<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Documents par niveau</p>
        <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $programmeNiveau->niveau->libelle }}</h1>
        <p class="mt-4 max-w-3xl leading-7 text-purple-100">
            Programme : {{ $programmeNiveau->programme->nom }}. Sélectionnez les pièces demandées, leur caractère obligatoire et leur ordre d’affichage.
        </p>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
            <p class="font-bold">La configuration n’a pas pu être enregistrée.</p>
            <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Documents disponibles</p>
        <h2 class="mt-2 text-2xl font-bold">Configuration active</h2>
        <p class="mt-3 max-w-4xl leading-7 text-epf-muted">Un document facultatif est proposé au candidat, mais son absence ne bloque pas la soumission.</p>

        <form method="POST" action="{{ route('administration.programme-niveaux.documents.update', $programmeNiveau) }}" class="mt-7">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto rounded-2xl border border-purple-100">
                <table class="min-w-full divide-y divide-purple-100">
                    <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                        <tr><th class="px-5 py-4">Demandé</th><th class="px-5 py-4">Document</th><th class="px-5 py-4">Obligatoire</th><th class="px-5 py-4">Ordre</th></tr>
                    </thead>
                    <tbody class="divide-y divide-purple-100">
                        @forelse ($documentsActifs as $document)
                            @php
                                $association = $associations->get($document->id);
                                $selectionne = (bool) old("documents.{$document->id}.selectionne", $association !== null);
                                $obligatoire = (bool) old("documents.{$document->id}.obligatoire", $association?->pivot->obligatoire ?? true);
                                $ordre = old("documents.{$document->id}.ordre", $association?->pivot->ordre ?? $loop->iteration);
                            @endphp
                            <tr>
                                <td class="px-5 py-5">
                                    <input type="hidden" name="documents[{{ $document->id }}][selectionne]" value="0">
                                    <input id="selectionne-{{ $document->id }}" name="documents[{{ $document->id }}][selectionne]" type="checkbox" value="1" @checked($selectionne) class="size-5 rounded border-purple-300 text-epf-purple focus:ring-epf-purple">
                                </td>
                                <td class="px-5 py-5"><label for="selectionne-{{ $document->id }}" class="cursor-pointer font-bold">{{ $document->libelle }}</label><p class="mt-1 font-mono text-xs text-epf-muted">{{ $document->code }}</p></td>
                                <td class="px-5 py-5">
                                    <input type="hidden" name="documents[{{ $document->id }}][obligatoire]" value="0">
                                    <label class="inline-flex cursor-pointer items-center gap-2"><input name="documents[{{ $document->id }}][obligatoire]" type="checkbox" value="1" @checked($obligatoire) class="rounded border-purple-300 text-epf-purple focus:ring-epf-purple"><span class="text-sm font-semibold">Oui</span></label>
                                </td>
                                <td class="px-5 py-5"><input name="documents[{{ $document->id }}][ordre]" type="number" min="1" max="999" value="{{ $ordre }}" required class="w-24 rounded-xl border-purple-200 text-sm focus:border-epf-purple focus:ring-epf-purple" aria-label="Ordre de {{ $document->libelle }}"></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-epf-muted">Aucun type de document actif n’est disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-7 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white focus:outline-none focus:ring-4 focus:ring-red-200">Enregistrer la configuration</button>
                <a href="{{ route('administration.programmes.edit', $programmeNiveau->programme) }}" class="rounded-xl border border-purple-200 px-6 py-3 font-semibold text-epf-purple">Retour au programme</a>
            </div>
        </form>
    </section>

    @if ($documentsInactifsAssocies->isNotEmpty())
        <section class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-amber-900">Associations inactives conservées</h2>
            <p class="mt-3 text-amber-800">Ces documents ne sont plus demandés aux nouveaux candidats, mais restent enregistrés pour l’historique.</p>
            <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ($documentsInactifsAssocies as $document)
                    <li class="rounded-xl border border-amber-200 bg-white px-4 py-3">
                        <p class="font-semibold text-amber-900">{{ $document->libelle }}</p>
                        <p class="mt-1 text-xs text-amber-700">{{ $document->pivot->obligatoire ? 'Obligatoire' : 'Facultatif' }} — ordre {{ $document->pivot->ordre }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-administration-layout>
