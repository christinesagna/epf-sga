<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Nouveau programme</p>
        <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Créer une formation</h1>
        <p class="mt-4 max-w-3xl leading-7 text-purple-100">
            Le programme sera créé inactif. Vous pourrez l’ouvrir aux candidatures après lui avoir associé au moins un niveau actif.
        </p>
    </section>

    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('administration.programmes.store') }}">
            @include('back-office.administration.programmes._form')
        </form>
    </section>
</x-administration-layout>
