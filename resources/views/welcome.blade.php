@extends('layouts.app')

@section('title', 'Bienvenue')

@section('content')
    {{-- Styles moved to resources/css/app.css --}}

    {{-- Navbar is now provided by the global layout --}}

    <section class="hero">
        <div class="hero-inner">

            <div class="hero-text">
                <span class="hero-badge fade-in fade-in-1">
                    <span class="hero-badge-dot"></span>
                    EPF AFRICA
                </span>

                <h1 class="hero-title fade-in fade-in-2">
                    Votre avenir<br>
                    commence ici,<br>
                    avec <span class="accent">EPF</span> <span class="accent-blue">Africa</span>
                </h1>

                <p class="hero-desc fade-in fade-in-3">
                    Rejoignez une école d'ingénierie d'excellence en Afrique. Candidature simple, rapide et 100&nbsp;% en ligne.
                </p>

                <div class="hero-buttons fade-in fade-in-4">
                    <a href="{{ route('candidatures.create') }}" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Soumettre une candidature
                    </a>
                    <a href="{{ route('programmes.index') }}" class="btn btn-secondary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Découvrir nos programmes
                    </a>
                </div>

                <p class="hero-note fade-in fade-in-5">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Processus sécurisé et transparent
                </p>
            </div>

            {{-- Colonne image (photo.jpg doit se trouver dans public/) --}}
            <div class="hero-visual fade-in fade-in-3">
                <div class="hero-wave"></div>
                <div class="hero-dots"></div>
                <img src="{{ asset('photo.jpg') }}" alt="Immeuble EPF Africa" class="hero-image" />
            </div>
        </div>
    </section>

    <section class="process">
        <div class="process-inner">
            <h2 class="process-title">
                Un processus d'admission simple et transparent
            </h2>

            <div class="process-underline"></div>

            <div class="process-grid">

                <div class="process-card">
                    <div class="process-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="process-step-title">1. Candidature en ligne</p>
                    <p class="process-step-desc">
                        Remplissez le formulaire de candidature en quelques minutes.
                    </p>
                </div>

                <div class="process-card">
                    <div class="process-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a7 7 0 100 14 7 7 0 000-14zm8 16l-4.35-4.35"/></svg>
                    </div>
                    <p class="process-step-title">2. Étude du dossier</p>
                    <p class="process-step-desc">
                        Votre dossier est analysé par nos équipes pédagogiques.
                    </p>
                </div>

                <div class="process-card">
                    <div class="process-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 2a4 4 0 100-8"/></svg>
                    </div>
                    <p class="process-step-title">3. Évaluation</p>
                    <p class="process-step-desc">
                        Passez les évaluations et entretiens selon le programme choisi.
                    </p>
                </div>

                <div class="process-card">
                    <div class="process-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="process-step-title">4. Résultat</p>
                    <p class="process-step-desc">
                        Recevez la décision d'admission et rejoignez l'aventure EPF Africa.
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection