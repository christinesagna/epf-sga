@extends('layouts.app')

@section('title', 'Candidature | SGA EPF')

@section('content')
    {{-- Section pleine largeur : fond slate-100 sur TOUT l'écran,
         contenu intérieure centré via container mx-auto --}}
    <section class="application-page-wrapper" style="background:linear-gradient(135deg,#fdf1f6 0%,#f8fafc 50%,#eef2ff 100%); width:100%; padding:32px 0 64px; min-height:calc(100vh - 72px);">
        <div class="container mx-auto" style="max-width:1280px; padding-left:1rem; padding-right:1rem;">
            <livewire:candidature.formulaire />
        </div>
    </section>
@endsection
