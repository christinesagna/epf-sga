<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Espace jury — EPF Africa</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-epf-lavender font-sans text-epf-purple antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            <aside class="hidden border-r border-purple-100 bg-epf-purple-dark text-white lg:flex lg:min-h-screen lg:flex-col">
                <a href="{{ route('jury.dashboard') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-6 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-white/20">
                    <x-application-logo class="h-14 w-14 rounded-xl bg-white object-contain p-1" />
                    <div>
                        <p class="text-lg font-bold">Espace jury</p>
                        <p class="text-xs text-purple-200">EPF Africa</p>
                    </div>
                </a>

                <nav class="flex-1 space-y-2 px-4 py-6" aria-label="Navigation du jury">
                    <a href="{{ route('jury.dashboard') }}" @if (request()->routeIs('jury.dashboard')) aria-current="page" @endif @class([
                        'flex items-center gap-3 rounded-xl px-4 py-3 font-semibold focus:outline-none focus:ring-4 focus:ring-white/20',
                        'bg-white text-epf-purple shadow-sm' => request()->routeIs('jury.dashboard'),
                        'text-purple-100 hover:bg-white/10' => ! request()->routeIs('jury.dashboard'),
                    ])>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-purple-100 text-xs font-bold">TB</span>
                        Tableau de bord
                    </a>

                    <a href="{{ route('jury.candidatures.index') }}" @if (request()->routeIs('jury.candidatures.*') || request()->routeIs('jury.documents.*')) aria-current="page" @endif @class([
                        'flex items-center gap-3 rounded-xl px-4 py-3 font-semibold focus:outline-none focus:ring-4 focus:ring-white/20',
                        'bg-white text-epf-purple shadow-sm' => request()->routeIs('jury.candidatures.*') || request()->routeIs('jury.documents.*'),
                        'text-purple-100 hover:bg-white/10' => ! request()->routeIs('jury.candidatures.*') && ! request()->routeIs('jury.documents.*'),
                    ])>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-white/10 text-xs font-bold">DO</span>
                        Dossiers
                    </a>
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
                                <p class="font-bold">Espace jury</p>
                                <p class="text-xs text-epf-muted">EPF Africa</p>
                            </div>
                        </div>

                        <div class="hidden lg:block">
                            <p class="text-sm font-semibold text-epf-purple">Espace sécurisé</p>
                            <p class="text-xs text-epf-muted">Étude des dossiers transmis au jury</p>
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
                        <nav class="grid gap-2 border-t border-purple-100 bg-epf-lavender p-4" aria-label="Navigation mobile du jury">
                            <a href="{{ route('jury.dashboard') }}" @class([
                                'rounded-xl px-4 py-3 font-semibold',
                                'bg-epf-purple text-white' => request()->routeIs('jury.dashboard'),
                                'border border-purple-100 bg-white text-epf-purple' => ! request()->routeIs('jury.dashboard'),
                            ])>Tableau de bord</a>
                            <a href="{{ route('jury.candidatures.index') }}" @class([
                                'rounded-xl px-4 py-3 font-semibold',
                                'bg-epf-purple text-white' => request()->routeIs('jury.candidatures.*') || request()->routeIs('jury.documents.*'),
                                'border border-purple-100 bg-white text-epf-purple' => ! request()->routeIs('jury.candidatures.*') && ! request()->routeIs('jury.documents.*'),
                            ])>Dossiers</a>
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
