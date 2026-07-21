@extends('layouts.app')

@section('title', 'Bienvenue')

@section('content')
    <style>
        /* ===== VARIABLES DE COULEUR ===== */
        :root {
            --primary: #9d174d;
            --primary-dark: #7f1d52;
            --primary-light: #fdf1f6;
            --accent-blue: #1e3a8a;
            --accent-blue-light: #eef2ff;
            --accent-blue-soft: #dbeafe;
        }

        /* Global reset */
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color:#111827; }
        img { max-width:100%; height:auto; display:block; }
        svg { display:block; }
        a { text-decoration:none; }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(18px); }
            to { opacity:1; transform:translateY(0); }
        }
        @keyframes floatY {
            0%, 100% { transform:translateY(0); }
            50% { transform:translateY(-10px); }
        }
        @keyframes gradientShift {
            0% { background-position:0% 50%; }
            50% { background-position:100% 50%; }
            100% { background-position:0% 50%; }
        }

        .fade-in { opacity:0; animation:fadeInUp 0.7s ease-out forwards; }
        .fade-in-1 { animation-delay:0.05s; }
        .fade-in-2 { animation-delay:0.15s; }
        .fade-in-3 { animation-delay:0.25s; }
        .fade-in-4 { animation-delay:0.35s; }
        .fade-in-5 { animation-delay:0.45s; }

        /* ===== NAVBAR ===== */
        .navbar {
            background:rgba(255,255,255,0.9);
            backdrop-filter: blur(6px);
            padding:0px 0;
            border-bottom:1px solid #e5e7eb;
            position:relative;
            transition:box-shadow 0.3s ease;
        }
        .navbar-inner {
            width:100%;
            display:flex;
            align-items:center;
            justify-content:space-between;
            position:relative;
            padding:0 20px;
            max-width:1280px;
            margin:0 auto;
        }
        .logo-link {
            display:flex;
            align-items:center;
            gap:10px;
            color:#111827;
            font-weight:700;
        }
        .logo-link img {
            width:56px;
            height:56px;
            object-fit:cover;
            border-radius:8px;
        }
        .logo-link span {
            background:linear-gradient(90deg, var(--primary), var(--accent-blue));
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
            font-size:1.05rem;
        }
        .nav-actions {
            display:flex;
            gap:40px;
            align-items:center;
            position:absolute;
            left:80%;
            transform:translateX(-50%);
        }
        .header-link svg {
            margin-right:10px;
            width:18px;
            height:18px;
            flex-shrink:0;
        }
        .header-link {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:10px 18px;
            min-width:130px;
            border-radius:14px;
            background:var(--primary);
            color:#fff;
            font-weight:700;
            font-size:0.9rem;
            letter-spacing:0.01em;
            transition:background 0.2s ease, transform 0.2s ease;
        }
        .header-link:hover { background:var(--primary-dark); transform:translateY(-2px); }

        /* ===== HERO ===== */
        .hero {
            position:relative;
            overflow:hidden;
            margin-top:16px;
            background:linear-gradient(135deg, var(--primary-light) 0%, #ffffff 45%, var(--accent-blue-light) 100%);
            background-size:200% 200%;
            animation:gradientShift 14s ease infinite;
        }
        .hero-inner {
            max-width:1280px;
            margin:0 auto;
            display:grid;
            grid-template-columns:1fr;
            align-items:center;
            gap:48px;
            padding:28px 32px 56px;
            position:relative;
            z-index:1;
        }
        @media (min-width:768px) {
            .hero-inner { grid-template-columns:1fr 1fr; padding:32px 40px 80px; gap:48px; }
        }

        .hero-text { position:relative; z-index:10; }
        .hero-badge {
            display:inline-flex;
            align-items:center;
            gap:8px;
            border-radius:999px;
            background:var(--primary-light);
            padding:6px 16px;
            font-size:0.85rem;
            font-weight:600;
            color:var(--primary);
            border:1px solid #f5d3e4;
        }
        .hero-badge-dot {
            height:8px; width:8px;
            border-radius:50%;
            background:var(--primary);
        }
        .hero-title {
            margin-top:16px;
            font-size:2.75rem;
            font-weight:800;
            line-height:1.08;
            letter-spacing:-0.03em;
            color:#111827;
        }
        @media (min-width:640px) { .hero-title { font-size:3.75rem; } }
        .hero-title .accent { color:var(--primary); }
        .hero-title .accent-blue { color:var(--accent-blue); }

        .hero-desc {
            margin-top:24px;
            max-width:28rem;
            font-size:1rem;
            line-height:1.6;
            color:#4b5563;
        }

        .hero-buttons {
            margin-top:32px;
            display:flex;
            flex-direction:column;
            gap:16px;
        }
        @media (min-width:640px) { .hero-buttons { flex-direction:row; } }

        .btn {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            border-radius:8px;
            padding:12px 20px;
            font-size:0.88rem;
            font-weight:600;
            transition:all 0.22s ease;
        }
        .btn svg { height:14px; width:14px; }
        .btn-primary {
            background:linear-gradient(90deg, var(--primary), var(--primary-dark));
            color:#fff;
            box-shadow:0 4px 14px rgba(157,23,77,0.3);
        }
        .btn-primary:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(157,23,77,0.4); }
        .btn-secondary {
            background:#fff;
            color:var(--primary);
            border:1px solid #f0c9dc;
        }
        .btn-secondary:hover {
            background:var(--primary-light);
            transform:translateY(-3px);
            border-color:var(--primary);
        }

        .hero-note {
            margin-top:22px;
            display:flex;
            align-items:center;
            gap:10px;
            font-size:0.95rem;
            color:#6b7280;
        }
        .hero-note svg { height:16px; width:16px; color:var(--accent-blue); }

        /* Colonne image */
        .hero-visual { position:relative; width:100%; max-width:30rem; margin:0 auto; }
        @media (min-width:768px) { .hero-visual { max-width:32rem; } }
        .hero-wave {
            position:absolute;
            bottom:-20px; right:-20px;
            height:104%; width:104%;
            border-radius:32px;
            background:linear-gradient(to bottom right, var(--primary), var(--accent-blue));
            clip-path:polygon(15% 0%,100% 0%,100% 100%,0% 100%);
            z-index:0;
        }
        .hero-dots {
            position:absolute;
            left:-12px; top:-12px;
            z-index:0;
            height:72px; width:72px;
            background-image:radial-gradient(circle,#f0c9dc 1.5px, transparent 1.5px);
            background-size:12px 12px;
            animation:floatY 4s ease-in-out infinite;
        }
        .hero-image {
            position:relative;
            z-index:10;
            max-width:100%;
            width:100%;
            max-height:500px;
            height:auto;
            border-radius:24px;
            object-fit:cover;
            object-position:top center;
            box-shadow:0 25px 50px -12px rgba(30,58,138,0.25);
        }
        .hero-floating-badge {
            position:absolute;
            bottom:16px; left:16px;
            z-index:20;
            display:flex;
            align-items:center;
            gap:8px;
            border-radius:12px;
            background:#fff;
            padding:8px 12px;
            box-shadow:0 8px 12px -4px rgba(0,0,0,0.08);
            animation:floatY 5s ease-in-out infinite;
        }
        .hero-floating-badge .icon {
            display:flex;
            align-items:center;
            justify-content:center;
            height:32px; width:32px;
            border-radius:50%;
            background:var(--primary-light);
            font-size:0.7rem;
            font-weight:700;
            color:var(--primary);
        }
        .hero-floating-badge span.label {
            font-size:0.875rem;
            font-weight:600;
            color:#1f2937;
        }

        /* ===== PROCESSUS ===== */
        .process {
            background:linear-gradient(180deg, var(--accent-blue-light) 0%, #f9fafb 100%);
            padding:56px 0;
        }
        .process-inner { max-width:1280px; margin:0 auto; padding:0 24px; }
        @media (min-width:1024px) { .process-inner { padding:0 32px; } }
        .process-title {
            text-align:center;
            font-size:1.5rem;
            font-weight:700;
            color:#111827;
        }
        @media (min-width:640px) { .process-title { font-size:1.875rem; } }
        .process-underline {
            margin:12px auto 0;
            height:4px; width:56px;
            border-radius:999px;
            background:linear-gradient(90deg, var(--primary), var(--accent-blue));
        }
        .process-grid {
            margin-top:32px;
            display:grid;
            grid-template-columns:1fr;
            gap:16px;
        }
        @media (min-width:640px) { .process-grid { grid-template-columns:1fr 1fr; } }
        @media (min-width:1024px) { .process-grid { grid-template-columns:repeat(4,1fr); } }

        .process-card {
            border-radius:12px;
            border:1px solid #f3f4f6;
            background:#fff;
            padding:16px;
            transition:transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .process-card:hover {
            transform:translateY(-6px);
            box-shadow:0 12px 24px rgba(30,58,138,0.12);
            border-color:var(--accent-blue-soft);
        }
        .process-icon {
            display:flex;
            align-items:center;
            justify-content:center;
            height:44px; width:44px;
            border-radius:12px;
            background:var(--primary-light);
            color:var(--primary);
            transition:background 0.25s ease, color 0.25s ease;
        }
        .process-card:hover .process-icon {
            background:var(--accent-blue);
            color:#fff;
        }
        .process-icon svg { height:20px; width:20px; }
        .process-step-title {
            margin-top:12px;
            font-size:0.9rem;
            font-weight:700;
            color:var(--primary);
        }
        .process-step-desc {
            margin-top:6px;
            font-size:0.9rem;
            line-height:1.45;
            color:#4b5563;
        }
    </style>

    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ url('/') }}" class="logo-link">
                <img src="{{ asset('logo.jpg') }}" alt="Logo EPF SGA" />
                <span>EPF Africa</span>
            </a>
            <div class="nav-actions">
                <a href="{{ url('/') }}" class="header-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true" focusable="false">
                        <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1h-5v-5H9v5H4a1 1 0 01-1-1V9.5z" />
                    </svg>
                    Accueil
                </a>
                <a href="{{ route('programmes.index') }}" class="header-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true" focusable="false">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                        <path d="M4 6l4 4-4 4" />
                    </svg>
                    Programmes
                </a>
            </div>
        </div>
    </nav>

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
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Soumettre une candidature
                    </a>
                    <a href="{{ route('programmes.index') }}" class="btn btn-secondary">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Découvrir nos programmes
                    </a>
                </div>

                <p class="hero-note fade-in fade-in-5">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="process-step-title">1. Candidature en ligne</p>
                    <p class="process-step-desc">
                        Remplissez le formulaire de candidature en quelques minutes.
                    </p>
                </div>

                <div class="process-card">
                    <div class="process-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a7 7 0 100 14 7 7 0 000-14zm8 16l-4.35-4.35"/></svg>
                    </div>
                    <p class="process-step-title">2. Étude du dossier</p>
                    <p class="process-step-desc">
                        Votre dossier est analysé par nos équipes pédagogiques.
                    </p>
                </div>

                <div class="process-card">
                    <div class="process-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 2a4 4 0 100-8"/></svg>
                    </div>
                    <p class="process-step-title">3. Évaluation</p>
                    <p class="process-step-desc">
                        Passez les évaluations et entretiens selon le programme choisi.
                    </p>
                </div>

                <div class="process-card">
                    <div class="process-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
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