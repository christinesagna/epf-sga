<x-guest-layout>
    <x-slot name="title">Connexion</x-slot>

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-epf-red">Back-office</p>
        <h2 class="mt-3 text-3xl font-bold text-epf-purple">Bienvenue</h2>
        <p class="mt-3 leading-7 text-epf-muted">Connectez-vous avec votre adresse professionnelle pour accéder à votre espace.</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" value="Email professionnel" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="prenom.nom@epf.fr" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-4">
                <x-input-label for="password" value="Mot de passe" />
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-epf-red underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-red-100">
                    Mot de passe oublié ?
                </a>
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember" class="flex cursor-pointer items-center gap-3 text-sm text-epf-muted">
            <input id="remember" type="checkbox" name="remember" class="rounded border-gray-300 text-epf-purple focus:ring-epf-purple">
            Rester connecté sur cet appareil
        </label>

        <x-primary-button class="w-full">Se connecter</x-primary-button>
    </form>
</x-guest-layout>
