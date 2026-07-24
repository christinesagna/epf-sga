@csrf
@if ($typeDocument->exists)
    @method('PUT')
@endif

<div class="grid gap-6">
    <div>
        <x-input-label for="libelle" value="Libellé du document" />
        <x-text-input id="libelle" name="libelle" type="text" :value="old('libelle', $typeDocument->libelle)" required autofocus placeholder="Exemple : Attestation de réussite" />
        <x-input-error :messages="$errors->get('libelle')" />
    </div>

    @if ($typeDocument->exists)
        <div class="rounded-2xl bg-epf-lavender px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-wide text-epf-muted">Code technique stable</p>
            <p class="mt-2 font-mono font-semibold text-epf-purple">{{ $typeDocument->code }}</p>
            <p class="mt-2 text-sm text-epf-muted">Le renommage ne modifie pas ce code afin de préserver les dossiers existants.</p>
        </div>
    @endif

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple" placeholder="Précisez le contenu attendu.">{{ old('description', $typeDocument->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>

    <fieldset>
        <legend class="text-sm font-medium text-epf-purple">Extensions autorisées</legend>
        <p class="mt-1 text-sm text-epf-muted">Sélectionnez au moins un format accepté.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($extensions as $extension => $libelle)
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-purple-100 bg-white px-4 py-3 focus-within:ring-4 focus-within:ring-purple-100">
                    <input name="extensions_autorisees[]" type="checkbox" value="{{ $extension }}" @checked(in_array($extension, old('extensions_autorisees', $typeDocument->extensions_autorisees ?? []), true)) class="rounded border-purple-300 text-epf-purple focus:ring-epf-purple">
                    <span class="font-semibold">{{ $libelle }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('extensions_autorisees')" />
        <x-input-error :messages="$errors->get('extensions_autorisees.*')" />
    </fieldset>

    <div class="max-w-xs">
        <x-input-label for="taille_max_mb" value="Taille maximale (Mo)" />
        <input id="taille_max_mb" name="taille_max_mb" type="number" min="1" max="50" value="{{ old('taille_max_mb', $typeDocument->taille_max_mb ?? 5) }}" required class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
        <p class="mt-2 text-sm text-epf-muted">Valeur autorisée : de 1 à 50 Mo.</p>
        <x-input-error :messages="$errors->get('taille_max_mb')" />
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200">
        {{ $typeDocument->exists ? 'Enregistrer les modifications' : 'Créer le type de document' }}
    </button>
    <a href="{{ route('administration.documents.index') }}" class="rounded-xl border border-purple-200 px-6 py-3 font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">Annuler</a>
</div>
