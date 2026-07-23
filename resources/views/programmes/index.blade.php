@extends('layouts.app')

@section('title', 'Programmes')

@section('content')
<style>
    :root {
        --primary: #9d174d;
        --primary-dark: #7f1d52;
        --accent-blue: #1e3a8a;
        --surface: #ffffff;
        --text: #111827;
        --muted: #4b5563;
        --border: #e5e7eb;
    }

    body { margin:0; padding:0; }
    .navbar {
        background:rgba(255,255,255,0.95);
        backdrop-filter: blur(6px);
        padding:0;
        border-bottom:1px solid var(--border);
        position:relative;
        transition:box-shadow 0.3s ease;
        min-height:56px;
    }
    .navbar-inner {
        width:100%;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        position:relative;
        padding:8px 24px;
        max-width:1280px;
        margin:0 auto;
        min-height:56px;
    }
    .logo-link {
        display:flex;
        align-items:center;
        gap:10px;
        color:var(--text);
        font-weight:700;
    }
    .logo-link img {
        width:56px;
        height:56px;
        object-fit:cover;
        border-radius:12px;
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
        gap:20px;
        align-items:center;
    }
    .header-link {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:10px;
        padding:12px 20px;
        border-radius:14px;
        background:var(--primary);
        color:#fff;
        font-weight:700;
        font-size:0.95rem;
        letter-spacing:0.01em;
        transition:background 0.2s ease, transform 0.2s ease;
    }
    .header-link:hover {
        background:var(--primary-dark);
        transform:translateY(-2px);
    }
    .page-section {
        max-width:1200px;
        margin:0 auto;
        padding:40px 24px 80px;
    }
    .page-intro {
        margin-bottom:32px;
    }
    .page-intro h1 {
        margin:0 0 12px;
        font-size:2.8rem;
        color:var(--text);
    }
    .page-intro p {
        margin:0;
        color:var(--muted);
        font-size:1.05rem;
        line-height:1.8;
    }
    .programme-grid {
        display:grid;
        gap:24px;
    }
    @media (min-width:768px) {
        .programme-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }
    .programme-card {
        background:var(--surface);
        border:1px solid var(--border);
        border-radius:24px;
        padding:28px;
        box-shadow:0 20px 60px rgba(17,24,39,0.05);
    }
    .programme-card h2 {
        margin:0 0 10px;
        font-size:1.45rem;
        color:var(--text);
    }
    .programme-card p {
        color:var(--muted);
        line-height:1.8;
        margin-bottom:18px;
    }
    .programme-actions {
        display:flex;
        flex-wrap:wrap;
        gap:12px;
    }
    .programme-meta {
        text-transform:uppercase;
        letter-spacing:.12em;
        font-size:.82rem;
        color:var(--primary);
        font-weight:700;
        margin-bottom:14px;
    }
    .btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        padding:12px 18px;
        border-radius:14px;
        font-weight:700;
        text-decoration:none;
        transition:transform 0.2s ease, background 0.2s ease;
    }
    .btn-primary {
        background:var(--primary);
        color:#fff;
    }
    .btn-primary:hover {
        background:var(--primary-dark);
        transform:translateY(-2px);
    }
    .btn-secondary {
        background:#fff;
        color:var(--primary);
        border:1px solid var(--primary);
    }
    .btn-secondary:hover {
        background:#fdf1f6;
        transform:translateY(-2px);
    }
</style>

{{-- Navbar moved to global layout --}}

<div class="page-section">
    <div class="page-intro">
        <h1>Nos parcours de formation</h1>
        <p>Choisissez un parcours pour découvrir les détails et soumettre votre candidature.</p>
    </div>

    <div class="programme-grid">
        @foreach ($categories as $slug => $category)
            <article class="programme-card">
                <div class="programme-meta">
                    {{ $category['nombre_programmes'] }} programme(s) disponible(s)
                </div>
                <h2>{{ $category['titre'] }}</h2>
                <p>{{ $category['description'] }}</p>
                <div class="programme-actions">
                    <a href="{{ route('programmes.show', $slug) }}" class="btn btn-primary">Voir le détail</a>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
