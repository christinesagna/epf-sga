<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Administration — EPF Africa</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-epf-lavender font-sans text-epf-purple antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[18rem_1fr]">
            <aside class="hidden border-r border-purple-100 bg-epf-purple-dark text-white lg:flex lg:min-h-screen lg:flex-col">
                <a href="{{ route('administration.dashboard') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-6 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-white/20">
                    <x-application-logo class="h-14 w-14 rounded-xl bg-white object-contain p-1" />
                    <div>
                        <p class="text-lg font-bold">Administration</p>
                        <p class="text-xs text-purple-200">EPF Africa</p>
                    </div>
                </a>

                <nav class="flex-1 space-y-2 px-4 py-6" aria-label="Navigation de l'administration">
                    <a href="{{ route('administration.dashboard') }}" @if (request()->routeIs('administration.dashboard')) aria-current="page" @endif @class([
                        'flex items-center gap-3 rounded-xl px-4 py-3 font-semibold focus:outline-none focus:ring-4 focus:ring-white/20',
                        'bg-white text-epf-purple shadow-sm' => request()->routeIs('administration.dashboard'),
                        'text-purple-100 hover:bg-white/10' => ! request()->routeIs('administration.dashboard'),
                    ])>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-purple-100 text-xs font-bold">TB</span>
                        Tableau de bord
                    </a>

                    <a href="{{ route('administration.utilisateurs.index') }}" @if (request()->routeIs('administration.utilisateurs.*')) aria-current="page" @endif @class([
                        'flex items-center gap-3 rounded-xl px-4 py-3 font-semibold focus:outline-none focus:ring-4 focus:ring-white/20',
                        'bg-white text-epf-purple shadow-sm' => request()->routeIs('administration.utilisateurs.*'),
                        'text-purple-100 hover:bg-white/10' => ! request()->routeIs('administration.utilisateurs.*'),
                    ])>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-white/10 text-xs font-bold">UT</span>
                        Utilisateurs
                    </a>

                    @foreach ([
                        ['code' => 'PR', 'libelle' => 'Programmes'],
                        ['code' => 'DO', 'libelle' => 'Documents'],
                        ['code' => 'CA', 'libelle' => 'Candidatures'],
                    ] as $module)
                        <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-purple-100" aria-disabled="true">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-white/10 text-xs font-bold">{{ $module['code'] }}</span>
                            <span class="flex-1">{{ $module['libelle'] }}</span>
                            <span class="rounded-full bg-white/10 px-2 py-1 text-[0.65rem] font-bold uppercase tracking-wide">Bientôt</span>
                        </span>
                    @endforeach
                </nav>

                <div class="border-t border-white/10 px-6 py-5 text-sm">
                    <p class="font-semibold">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-xs text-purple-200">{{ auth()->user()->role->libelle() }}</p>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="border-b border-purple-100 bg-white">
                    <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-8 lg:px-10">
                        <div class="flex items-center gap-3 lg:hidden">
                            <x-application-logo class="h-12 w-12 rounded-lg object-contain" />
                            <div>
                                <p class="font-bold">Administration</p>
                                <p class="text-xs text-epf-muted">EPF Africa</p>
                            </div>
                        </div>

                        <div class="hidden lg:block">
                            <p class="text-sm font-semibold text-epf-purple">Espace sécurisé</p>
                            <p class="text-xs text-epf-muted">Pilotage du back-office</p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple transition hover:border-epf-red hover:text-epf-red focus:outline-none focus:ring-4 focus:ring-purple-100">
                                Déconnexion
                            </button>
                        </form>
                    </div>

                    <details class="border-t border-purple-100 lg:hidden">
                        <summary class="cursor-pointer px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-inset focus:ring-purple-100">
                            Ouvrir la navigation
                        </summary>
                        <nav class="grid gap-2 border-t border-purple-100 bg-epf-lavender p-4" aria-label="Navigation mobile de l'administration">
                            <a href="{{ route('administration.dashboard') }}" @class([
                                'rounded-xl px-4 py-3 font-semibold',
                                'bg-epf-purple text-white' => request()->routeIs('administration.dashboard'),
                                'border border-purple-100 bg-white text-epf-purple' => ! request()->routeIs('administration.dashboard'),
                            ])>Tableau de bord</a>
                            <a href="{{ route('administration.utilisateurs.index') }}" @class([
                                'rounded-xl px-4 py-3 font-semibold',
                                'bg-epf-purple text-white' => request()->routeIs('administration.utilisateurs.*'),
                                'border border-purple-100 bg-white text-epf-purple' => ! request()->routeIs('administration.utilisateurs.*'),
                            ])>Utilisateurs</a>
                            @foreach (['Programmes', 'Documents', 'Candidatures'] as $module)
                                <span class="flex cursor-not-allowed items-center justify-between rounded-xl border border-purple-100 bg-white px-4 py-3 text-sm text-epf-muted" aria-disabled="true">
                                    {{ $module }}
                                    <span class="text-xs font-bold uppercase">Bientôt</span>
                                </span>
                            @endforeach
                        </nav>
                    </details>
                </header>

                <main class="px-5 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
