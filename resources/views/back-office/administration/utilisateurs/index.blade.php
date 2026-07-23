<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Administration</p>
        <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Gestion des utilisateurs internes</h1>
        <p class="mt-4 max-w-3xl leading-7 text-purple-100">
            Invitez les équipes d’admission et les membres du jury, attribuez leurs rôles et contrôlez leurs accès au back-office.
        </p>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
            <p class="font-bold">L’action n’a pas pu être enregistrée.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="invitation-title">
        <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Nouveau compte</p>
            <h2 id="invitation-title" class="mt-2 text-2xl font-bold">Inviter un utilisateur</h2>
            <p class="mt-3 leading-7 text-epf-muted">
                L’utilisateur recevra un lien valable 60 minutes pour définir son mot de passe. Aucun mot de passe ne lui est attribué par l’administrateur.
            </p>
        </div>

        <form method="POST" action="{{ route('administration.utilisateurs.store') }}" class="mt-7 grid gap-5 lg:grid-cols-[1fr_1.2fr_1fr_auto] lg:items-end">
            @csrf

            <div>
                <x-input-label for="name" value="Nom complet" />
                <x-text-input id="name" name="name" type="text" :value="old('name')" required autocomplete="name" />
            </div>

            <div>
                <x-input-label for="email" value="Email professionnel" />
                <x-text-input id="email" name="email" type="email" :value="old('email')" required autocomplete="email" />
            </div>

            <div>
                <x-input-label for="role" value="Rôle" />
                <select id="role" name="role" required class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->libelle() }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="rounded-xl bg-epf-red px-6 py-3 font-bold text-white transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200">
                Créer et inviter
            </button>
        </form>
    </section>

    <section class="mt-8" aria-labelledby="comptes-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-epf-red">Accès internes</p>
                <h2 id="comptes-title" class="mt-2 text-2xl font-bold">Comptes internes</h2>
            </div>
            <p class="text-sm font-semibold text-epf-muted">{{ $utilisateurs->total() }} utilisateur(s)</p>
        </div>

        <form method="GET" action="{{ route('administration.utilisateurs.index') }}" class="mt-5 grid gap-4 rounded-2xl border border-purple-100 bg-white p-5 md:grid-cols-[1.4fr_1fr_1fr_auto] md:items-end">
            <div>
                <x-input-label for="recherche" value="Rechercher" />
                <x-text-input id="recherche" name="recherche" type="search" :value="$filtres['recherche'] ?? ''" placeholder="Nom ou email" />
            </div>

            <div>
                <x-input-label for="filtre-role" value="Rôle" />
                <select id="filtre-role" name="role" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    <option value="">Tous les rôles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(($filtres['role'] ?? '') === $role->value)>{{ $role->libelle() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="etat" value="État" />
                <select id="etat" name="etat" class="mt-1 block w-full rounded-xl border-purple-200 text-epf-purple shadow-sm focus:border-epf-purple focus:ring-epf-purple">
                    <option value="">Tous les états</option>
                    <option value="actif" @selected(($filtres['etat'] ?? '') === 'actif')>Actif</option>
                    <option value="inactif" @selected(($filtres['etat'] ?? '') === 'inactif')>Inactif</option>
                    <option value="invitation" @selected(($filtres['etat'] ?? '') === 'invitation')>Invitation en attente</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-epf-purple px-5 py-3 font-semibold text-white focus:outline-none focus:ring-4 focus:ring-purple-200">Filtrer</button>
                <a href="{{ route('administration.utilisateurs.index') }}" class="rounded-xl border border-purple-200 px-5 py-3 font-semibold text-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">Effacer</a>
            </div>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-purple-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-purple-100">
                <thead class="bg-epf-lavender text-left text-xs font-bold uppercase tracking-wide text-epf-muted">
                    <tr>
                        <th class="px-5 py-4">Utilisateur</th>
                        <th class="px-5 py-4">Rôle</th>
                        <th class="px-5 py-4">État</th>
                        <th class="px-5 py-4">Dernière connexion</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-100">
                    @forelse ($utilisateurs as $utilisateur)
                        @php
                            $invitationEnAttente = ! $utilisateur->actif
                                && $utilisateur->email_verified_at === null
                                && $utilisateur->invitation_sent_at !== null;
                        @endphp
                        <tr class="align-top">
                            <td class="px-5 py-5">
                                <p class="font-bold text-epf-purple">{{ $utilisateur->name }}</p>
                                <p class="mt-1 text-sm text-epf-muted">{{ $utilisateur->email }}</p>
                            </td>
                            <td class="px-5 py-5">
                                <form method="POST" action="{{ route('administration.utilisateurs.role', $utilisateur) }}" class="flex min-w-56 gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" aria-label="Rôle de {{ $utilisateur->name }}" class="block w-full rounded-xl border-purple-200 text-sm text-epf-purple focus:border-epf-purple focus:ring-epf-purple">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}" @selected($utilisateur->role === $role)>{{ $role->libelle() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-xl border border-purple-200 px-3 text-sm font-semibold text-epf-purple hover:border-epf-purple focus:outline-none focus:ring-4 focus:ring-purple-100">Enregistrer</button>
                                </form>
                            </td>
                            <td class="px-5 py-5">
                                @if ($invitationEnAttente)
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Invitation en attente</span>
                                    <p class="mt-2 text-xs text-epf-muted">Envoyée le {{ $utilisateur->invitation_sent_at->format('d/m/Y à H:i') }}</p>
                                @elseif ($utilisateur->actif)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">Actif</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">Inactif</span>
                                @endif
                            </td>
                            <td class="px-5 py-5 text-sm text-epf-muted">
                                {{ $utilisateur->last_login_at?->format('d/m/Y à H:i') ?? 'Jamais' }}
                            </td>
                            <td class="px-5 py-5">
                                <div class="flex min-w-48 justify-end gap-2">
                                    @if ($invitationEnAttente)
                                        <form method="POST" action="{{ route('administration.utilisateurs.invitation.renvoyer', $utilisateur) }}">
                                            @csrf
                                            <button type="submit" class="rounded-xl border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-100">Renvoyer</button>
                                        </form>
                                    @elseif ($utilisateur->actif && ! auth()->user()->is($utilisateur))
                                        <form method="POST" action="{{ route('administration.utilisateurs.etat', $utilisateur) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="actif" value="0">
                                            <button type="submit" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-epf-red hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Désactiver</button>
                                        </form>
                                    @elseif (! $utilisateur->actif)
                                        <form method="POST" action="{{ route('administration.utilisateurs.etat', $utilisateur) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="actif" value="1">
                                            <button type="submit" class="rounded-xl border border-green-200 px-4 py-2 text-sm font-semibold text-green-800 hover:bg-green-50 focus:outline-none focus:ring-4 focus:ring-green-100">Activer</button>
                                        </form>
                                    @else
                                        <span class="rounded-xl bg-purple-50 px-4 py-2 text-sm font-semibold text-epf-muted">Votre compte</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-epf-muted">Aucun utilisateur ne correspond aux filtres.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $utilisateurs->links() }}
        </div>
    </section>
</x-administration-layout>
