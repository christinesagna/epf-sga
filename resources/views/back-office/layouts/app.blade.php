<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Back-office — EPF Africa</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-epf-lavender font-sans text-epf-purple antialiased">
        <header class="border-b border-purple-100 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
                <a href="{{ route('back-office.dashboard') }}" class="flex items-center gap-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-100">
                    <x-application-logo class="h-14 w-14 rounded-lg object-contain" />
                    <div>
                        <p class="font-bold text-epf-purple">Back-office</p>
                        <p class="text-xs text-epf-muted">EPF Africa</p>
                    </div>
                </a>

                <div class="flex items-center gap-3 sm:gap-5">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-epf-muted">{{ auth()->user()->role->libelle() }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-purple-200 px-4 py-2 text-sm font-semibold text-epf-purple transition hover:border-epf-red hover:text-epf-red focus:outline-none focus:ring-4 focus:ring-purple-100">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
            {{ $slot }}
        </main>
    </body>
</html>
