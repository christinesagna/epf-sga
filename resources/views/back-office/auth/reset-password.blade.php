<x-guest-layout>
    <x-slot name="title">Nouveau mot de passe</x-slot>

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-epf-red">Sécurité du compte</p>
        <h2 class="mt-3 text-3xl font-bold text-epf-purple">Définir un nouveau mot de passe</h2>
        <p class="mt-3 leading-7 text-epf-muted">Choisissez un mot de passe personnel et difficile à deviner.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email professionnel" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Enregistrer le mot de passe</x-primary-button>
    </form>
</x-guest-layout>
