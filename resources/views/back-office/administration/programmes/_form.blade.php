@csrf
@if ($programme->exists)
    @method('PUT')
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="nom" value="Nom du programme" />
        <x-text-input id="nom" name="nom" type="text" :value="old('nom', $programme->nom)" required />
        <x-input-error :messages="$errors->get('nom')" class="mt-2" />
        @if ($programme->exists)
            <p class="mt-2 text-xs text-epf-muted">Adresse publique conservée : /programmes/{{ $programme->slug }}</p>
        @endif
    </div>

    <div>
        <x-input-label for="niveau" value="Cycle du programme" />
        <select id="niveau" name="niveau" required class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
            @foreach ($cycles as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected(old('niveau', $programme->niveau) === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('niveau')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="capacite_accueil" value="Capacité d’accueil" />
        <x-text-input id="capacite_accueil" name="capacite_accueil" type="number" min="0" :value="old('capacite_accueil', $programme->capacite_accueil ?? 0)" required />
        <x-input-error :messages="$errors->get('capacite_accueil')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="date_ouverture" value="Date d’ouverture" />
        <x-text-input id="date_ouverture" name="date_ouverture" type="date" :value="old('date_ouverture', $programme->date_ouverture?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('date_ouverture')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="date_fermeture" value="Date de fermeture" />
        <x-text-input id="date_fermeture" name="date_fermeture" type="date" :value="old('date_fermeture', $programme->date_fermeture?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('date_fermeture')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="frais_scolarite" value="Frais de scolarité" />
        <x-text-input id="frais_scolarite" name="frais_scolarite" type="number" min="0" step="0.01" :value="old('frais_scolarite', $programme->frais_scolarite)" />
        <x-input-error :messages="$errors->get('frais_scolarite')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="echeancier_paiement" value="Échéancier de paiement" />
        <textarea id="echeancier_paiement" name="echeancier_paiement" rows="3" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">{{ old('echeancier_paiement', $programme->echeancier_paiement) }}</textarea>
        <x-input-error :messages="$errors->get('echeancier_paiement')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">{{ old('description', $programme->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="mt-7 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200">
        {{ $programme->exists ? 'Enregistrer les modifications' : 'Créer le programme' }}
    </button>
    <a href="{{ route('administration.programmes.index') }}" class="rounded-xl border border-purple-200 px-6 py-3 font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">
        Retour à la liste
    </a>
</div>
