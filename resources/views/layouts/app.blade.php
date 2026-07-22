<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    {{-- Viewport AVEC maximum-scale=1 : empêche le zoom auto du navigateur qui cause "dézoomé" --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @hasSection('title') @yield('title')
        @elseif (!empty($title)) {{ $title }}
        @else {{ config('app.name', 'EPF Africa') }}
        @endif
    </title>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Vite gère TOUT : dev (HMR) + prod (manifest public/build/manifest.json).
         PAS besoin de fallback PHP bricolage : @vite() marche déjà dans les deux cas. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="antialiased bg-white text-gray-900"
      style="min-height:100vh; display:flex; flex-direction:column; margin:0; overflow-x:hidden; width:100%; max-width:100vw;">

    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ url('/') }}" class="logo-link">
                <img src="{{ asset('logo.jpg') }}" alt="Logo EPF SGA" />
                <span>EPF Africa</span>
            </a>
            <div class="nav-actions">
                <a href="{{ url('/') }}" class="header-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                        <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1h-5v-5H9v5H4a1 1 0 01-1-1V9.5z" />
                    </svg>
                    Accueil
                </a>
                <a href="{{ route('programmes.index') }}" class="header-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                        <path d="M4 6l4 4-4 4" />
                    </svg>
                    Programmes
                </a>
            </div>
        </div>
    </nav>

    <main style="flex:1; width:100%; display:flex; flex-direction:column;">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @livewireScripts
    @include('layouts.footer')
</body>
</html>
