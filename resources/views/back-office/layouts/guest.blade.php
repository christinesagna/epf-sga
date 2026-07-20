<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Authentification' }} — EPF Africa</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-epf-lavender font-sans text-epf-purple antialiased">
        <main class="grid min-h-screen lg:grid-cols-[minmax(380px,0.9fr)_minmax(520px,1.1fr)]">
            <section class="relative hidden overflow-hidden bg-epf-purple px-12 py-14 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute -left-24 -top-24 size-72 rounded-full border-[48px] border-white/5"></div>
                <div class="absolute -bottom-36 -right-24 size-96 rounded-full border-[64px] border-epf-red/20"></div>

                <a href="{{ url('/') }}" class="relative inline-flex w-fit rounded-2xl bg-white p-3 shadow-xl focus:outline-none focus:ring-4 focus:ring-white/40">
                    <x-application-logo class="h-24 w-24 rounded-xl object-contain" />
                </a>

                <div class="relative max-w-lg">
                    <p class="mb-4 text-sm font-bold uppercase tracking-[0.24em] text-red-200">Espace sécurisé</p>
                    <h1 class="text-4xl font-bold leading-tight xl:text-5xl">Pilotez les admissions de l’EPF Africa.</h1>
                    <p class="mt-6 max-w-md text-lg leading-8 text-purple-100">
                        Un accès réservé aux équipes d’admission, aux membres du jury et aux administrateurs.
                    </p>
                </div>

                <p class="relative text-sm text-purple-200">Engineering School — Creating the future together</p>
            </section>

            <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10 lg:px-16">
                <div class="w-full max-w-xl">
                    <a href="{{ url('/') }}" class="mb-8 inline-flex rounded-2xl bg-white p-2 shadow-sm focus:outline-none focus:ring-4 focus:ring-purple-100 lg:hidden">
                        <x-application-logo class="h-20 w-20 rounded-xl object-contain" />
                    </a>

                    <div class="rounded-3xl border border-white bg-white p-6 shadow-[0_24px_70px_rgba(38,0,82,0.12)] sm:p-10">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs leading-5 text-epf-muted">
                        Accès strictement réservé au personnel autorisé de l’EPF Africa.
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
