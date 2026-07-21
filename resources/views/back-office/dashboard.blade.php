<x-app-layout>
    <section class="overflow-hidden rounded-3xl bg-epf-purple px-6 py-9 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-10 sm:py-12">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-200">{{ auth()->user()->role->libelle() }}</p>
        <h1 class="mt-4 text-3xl font-bold sm:text-4xl">Bonjour {{ auth()->user()->name }}</h1>
        <p class="mt-4 max-w-2xl leading-7 text-purple-100">
            Votre accès au back-office est opérationnel. Les outils correspondant à votre rôle seront ajoutés au fil des prochaines étapes du projet.
        </p>
    </section>

    <section class="mt-8 grid gap-5 md:grid-cols-3">
        <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex size-11 items-center justify-center rounded-xl bg-purple-100 text-xl font-bold text-epf-purple">1</div>
            <h2 class="font-bold">Compte sécurisé</h2>
            <p class="mt-2 text-sm leading-6 text-epf-muted">Votre email est vérifié et votre session est protégée.</p>
        </article>

        <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex size-11 items-center justify-center rounded-xl bg-red-100 text-xl font-bold text-epf-red">2</div>
            <h2 class="font-bold">Rôle attribué</h2>
            <p class="mt-2 text-sm leading-6 text-epf-muted">{{ auth()->user()->role->libelle() }}</p>
        </article>

        <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex size-11 items-center justify-center rounded-xl bg-purple-100 text-xl font-bold text-epf-purple">3</div>
            <h2 class="font-bold">Espace en préparation</h2>
            <p class="mt-2 text-sm leading-6 text-epf-muted">Les fonctionnalités métier seront disponibles dans les prochaines versions.</p>
        </article>
    </section>
</x-app-layout>
