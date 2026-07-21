<x-guest-layout>
    <x-slot name="title">Vérification de l’email</x-slot>

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-epf-red">Dernière étape</p>
        <h2 class="mt-3 text-3xl font-bold text-epf-purple">Vérifiez votre adresse email</h2>
        <p class="mt-3 leading-7 text-epf-muted">Un lien de vérification a été envoyé à <strong class="text-epf-purple">{{ auth()->user()->email }}</strong>. Cliquez dessus pour accéder au back-office.</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            Un nouveau lien de vérification vient d’être envoyé.
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-primary-button class="w-full">Renvoyer le lien</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <button type="submit" class="min-h-12 w-full rounded-xl border border-purple-200 px-5 py-3 text-sm font-semibold text-epf-purple transition hover:border-epf-red hover:text-epf-red focus:outline-none focus:ring-4 focus:ring-purple-100">
                Se déconnecter
            </button>
        </form>
    </div>
</x-guest-layout>
