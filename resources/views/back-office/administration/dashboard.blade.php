<x-administration-layout>
    <section class="overflow-hidden rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9 sm:py-10">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Super-administration</p>
        <div class="mt-4 max-w-3xl">
            <h1 class="text-3xl font-bold sm:text-4xl">Bonjour {{ auth()->user()->name }}</h1>
            <p class="mt-4 leading-7 text-purple-100">
                Retrouvez une vue d’ensemble des accès internes, des programmes et des documents configurés pour les candidatures EPF Africa.
            </p>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="indicateurs-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Situation actuelle</p>
                <h2 id="indicateurs-title" class="mt-2 text-2xl font-bold">Indicateurs du back-office</h2>
            </div>
            <p class="text-sm text-epf-muted">Données actualisées à l’ouverture de la page</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['valeur' => $utilisateursInternes, 'libelle' => 'Comptes internes', 'detail' => 'Tous les rôles du back-office', 'couleur' => 'purple'],
                ['valeur' => $utilisateursActifs, 'libelle' => 'Comptes actifs', 'detail' => 'Connexion actuellement autorisée', 'couleur' => 'red'],
                ['valeur' => $invitationsEnAttente, 'libelle' => 'Emails à vérifier', 'detail' => 'Comptes internes non vérifiés', 'couleur' => 'purple'],
                ['valeur' => $programmesActifs, 'libelle' => 'Programmes actifs', 'detail' => 'Programmes actuellement disponibles', 'couleur' => 'red'],
                ['valeur' => $niveauxConfigures, 'libelle' => 'Niveaux configurés', 'detail' => 'Niveaux de formation du catalogue', 'couleur' => 'purple'],
                ['valeur' => $typesDocumentsActifs, 'libelle' => 'Types de documents', 'detail' => 'Documents actifs configurés', 'couleur' => 'red'],
            ] as $indicateur)
                <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-epf-muted">{{ $indicateur['libelle'] }}</p>
                            <p class="mt-2 text-4xl font-bold text-epf-purple">{{ $indicateur['valeur'] }}</p>
                        </div>
                        <span @class([
                            'size-3 rounded-full',
                            'bg-epf-purple' => $indicateur['couleur'] === 'purple',
                            'bg-epf-red' => $indicateur['couleur'] === 'red',
                        ])></span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-epf-muted">{{ $indicateur['detail'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-8 grid gap-5 xl:grid-cols-[1.35fr_1fr]">
        <article class="rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Candidatures</p>
                    <h2 class="mt-2 text-2xl font-bold">Module en préparation</h2>
                </div>
                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-epf-purple">À venir</span>
            </div>
            <p class="mt-4 max-w-2xl leading-7 text-epf-muted">
                Les indicateurs de soumission et de traitement seront activés lorsque le parcours candidat sera finalisé. Aucun dossier n’est consulté depuis cette page pour le moment.
            </p>
        </article>

        <article class="rounded-3xl bg-epf-purple-dark p-6 text-white shadow-sm sm:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-200">Progression</p>
            <h2 class="mt-2 text-2xl font-bold">Prochaine étape</h2>
            <p class="mt-4 leading-7 text-purple-100">
                La gestion des comptes internes permettra d’inviter les membres de l’admission et du jury, puis de gérer leurs rôles et leur activation.
            </p>
        </article>
    </section>
</x-administration-layout>
