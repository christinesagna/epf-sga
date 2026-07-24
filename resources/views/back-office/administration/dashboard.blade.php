<x-administration-layout>
    <section class="overflow-hidden rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9 sm:py-10">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Super-administration</p>
        <div class="mt-4 max-w-3xl">
            <h1 class="text-3xl font-bold sm:text-4xl">Bonjour {{ auth()->user()->name }}</h1>
            <p class="mt-4 leading-7 text-purple-100">
                Suivez l’activité des candidatures et retrouvez les principaux indicateurs de configuration du back-office EPF Africa.
            </p>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="candidatures-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Activité des dossiers</p>
                <h2 id="candidatures-title" class="mt-2 text-2xl font-bold">Suivi des candidatures</h2>
            </div>
            <p class="text-sm text-epf-muted">Les brouillons ne sont pas comptabilisés</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['valeur' => $indicateursCandidatures['total'], 'libelle' => 'Dossiers soumis', 'detail' => 'Toutes les candidatures entrées dans le workflow', 'couleur' => 'purple'],
                ['valeur' => $indicateursCandidatures['nouvelles'], 'libelle' => 'Nouvelles candidatures', 'detail' => 'En attente de prise en charge par l’admission', 'couleur' => 'red'],
                ['valeur' => $indicateursCandidatures['admission'], 'libelle' => 'En traitement admission', 'detail' => 'Dossiers actuellement étudiés par l’admission', 'couleur' => 'purple'],
                [
                    'valeur' => $indicateursCandidatures['complements'],
                    'libelle' => 'Compléments demandés',
                    'detail' => "Admission : {$indicateursCandidatures['complements_admission']} · Jury : {$indicateursCandidatures['complements_jury']}",
                    'couleur' => 'red',
                ],
                ['valeur' => $indicateursCandidatures['jury'], 'libelle' => 'Transmises au jury', 'detail' => 'Dossiers disponibles pour la délibération', 'couleur' => 'purple'],
                [
                    'valeur' => $indicateursCandidatures['decisions'],
                    'libelle' => 'Décisions rendues',
                    'detail' => "Admises : {$indicateursCandidatures['admises']} · Refusées : {$indicateursCandidatures['refusees']}",
                    'couleur' => 'red',
                ],
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

    <section class="mt-10" aria-labelledby="configuration-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Paramétrage</p>
                <h2 id="configuration-title" class="mt-2 text-2xl font-bold">Configuration du back-office</h2>
            </div>
            <p class="text-sm text-epf-muted">Données actualisées à l’ouverture de la page</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['valeur' => $utilisateursInternes, 'libelle' => 'Comptes internes', 'detail' => 'Tous les rôles du back-office', 'couleur' => 'purple'],
                ['valeur' => $utilisateursActifs, 'libelle' => 'Comptes actifs', 'detail' => 'Connexion actuellement autorisée', 'couleur' => 'red'],
                ['valeur' => $invitationsEnAttente, 'libelle' => 'Invitations en attente', 'detail' => 'Comptes qui doivent définir leur mot de passe', 'couleur' => 'purple'],
                ['valeur' => $programmesActifs, 'libelle' => 'Programmes actifs', 'detail' => 'Programmes actuellement disponibles', 'couleur' => 'red'],
                ['valeur' => $niveauxConfigures, 'libelle' => 'Niveaux configurés', 'detail' => 'Niveaux de formation du catalogue', 'couleur' => 'purple'],
                ['valeur' => $typesDocumentsActifs, 'libelle' => 'Types de documents', 'detail' => 'Documents actifs configurés', 'couleur' => 'red'],
            ] as $indicateur)
                <article class="rounded-2xl border border-purple-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-epf-muted">{{ $indicateur['libelle'] }}</p>
                            <p class="mt-2 text-3xl font-bold text-epf-purple">{{ $indicateur['valeur'] }}</p>
                        </div>
                        <span @class([
                            'size-3 rounded-full',
                            'bg-epf-purple' => $indicateur['couleur'] === 'purple',
                            'bg-epf-red' => $indicateur['couleur'] === 'red',
                        ])></span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-epf-muted">{{ $indicateur['detail'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-10 grid gap-5 xl:grid-cols-[1.15fr_1fr]" aria-labelledby="raccourcis-title">
        <article class="rounded-3xl bg-epf-purple-dark p-6 text-white shadow-sm sm:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-200">Accès rapides</p>
            <h2 id="raccourcis-title" class="mt-2 text-2xl font-bold">Gérer le back-office</h2>
            <p class="mt-3 leading-7 text-purple-100">
                Accédez aux modules administratifs actuellement disponibles.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('administration.utilisateurs.index') }}" class="rounded-xl bg-white px-5 py-3 font-bold text-epf-purple transition hover:bg-purple-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                    Gérer les utilisateurs
                </a>
                <a href="{{ route('administration.programmes.index') }}" class="rounded-xl border border-white/30 px-5 py-3 font-bold text-white transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20">
                    Gérer les programmes
                </a>
                <a href="{{ route('administration.documents.index') }}" class="rounded-xl border border-white/30 px-5 py-3 font-bold text-white transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20">
                    Gérer les documents
                </a>
                <a href="{{ route('administration.candidatures.index') }}" class="rounded-xl border border-white/30 px-5 py-3 font-bold text-white transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20">
                    Consulter les candidatures
                </a>
            </div>
        </article>

        <article class="rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Évolutions prévues</p>
            <h2 class="mt-2 text-2xl font-bold">Prochaine évolution administrative</h2>
            <div class="mt-5 flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-epf-lavender px-4 py-2 text-sm font-semibold text-epf-purple">
                    Historiques administratifs
                    <span class="rounded-full bg-white px-2 py-0.5 text-[0.65rem] font-bold uppercase text-epf-muted">À venir</span>
                </span>
            </div>
            <p class="mt-5 text-sm leading-6 text-epf-muted">
                L’écran dédié aux actions administratives sera ajouté dans une prochaine fonctionnalité.
            </p>
        </article>
    </section>
</x-administration-layout>
