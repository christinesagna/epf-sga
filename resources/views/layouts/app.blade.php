<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @hasSection('title')
            @yield('title')
        @elseif (!empty($title))
            {{ $title }}
        @else
            {{ config('app.name', 'EPF Africa') }}
        @endif
    </title>

    {{-- Load FontAwesome and Tailwind/Vite assets --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles
</head>
<body class="antialiased bg-white text-gray-900 min-h-screen flex flex-col" style="min-height:100vh; display:flex; flex-direction:column; margin:0;">
    <main class="flex-1" style="flex:1;">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @livewireScripts
    @include('layouts.footer')
</body>
</html>
