<x-guest-layout>
    <x-slot name="title">Mot de passe oublié</x-slot>

    <div class="mb-8">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-epf-red hover:underline">← Retour à la connexion</a>
        <h2 class="mt-5 text-3xl font-bold text-epf-purple">Mot de passe oublié ?</h2>
        <p class="mt-3 leading-7 text-epf-muted">Saisissez votre adresse professionnelle. Si votre compte est actif, vous recevrez un lien valable 60 minutes.</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" value="Email professionnel" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="prenom.nom@epf.fr" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Envoyer le lien</x-primary-button>
    </form>
</x-guest-layout>
