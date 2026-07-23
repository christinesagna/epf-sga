<x-guest-layout>
    <x-slot name="title">Activer mon compte</x-slot>

    @if ($lienValide)
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-epf-red">Invitation</p>
            <h2 class="mt-3 text-3xl font-bold text-epf-purple">Définissez votre mot de passe</h2>
            <p class="mt-3 leading-7 text-epf-muted">
                Ce mot de passe activera votre compte et confirmera votre adresse professionnelle.
            </p>
        </div>

        <form method="POST" action="{{ route('invitation.store') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <x-input-label for="email" value="Email professionnel" />
                <x-text-input id="email" type="email" name="email" :value="old('email', $email)" required readonly autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Nouveau mot de passe" />
                <x-text-input id="password" type="password" name="password" required autofocus autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <x-primary-button class="w-full">Activer mon compte</x-primary-button>
        </form>
    @else
        <div class="text-center">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-epf-red">Lien indisponible</p>
            <h2 class="mt-3 text-3xl font-bold text-epf-purple">Cette invitation n’est plus valide</h2>
            <p class="mt-4 leading-7 text-epf-muted">
                Le lien a expiré, a déjà été utilisé ou a été remplacé par une invitation plus récente. Contactez un administrateur pour recevoir un nouveau lien.
            </p>
            <a href="{{ route('login') }}" class="mt-7 inline-flex rounded-xl bg-epf-purple px-6 py-3 font-semibold text-white transition hover:bg-epf-purple-dark focus:outline-none focus:ring-4 focus:ring-purple-200">
                Retour à la connexion
            </a>
        </div>
    @endif
</x-guest-layout>
