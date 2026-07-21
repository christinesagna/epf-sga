@extends('layouts.app')

@section('title', $category['titre'])

@section('content')
<style>
    :root {
        --primary: #9d174d;
        --primary-dark: #7f1d52;
        --accent-blue: #1e3a8a;
        --surface: #ffffff;
        --border: #e5e7eb;
        --text: #111827;
        --muted: #4b5563;
    }

    .page-section {
        max-width:1120px;
        margin:0 auto;
        padding:40px 24px 80px;
    }
    .breadcrumb {
        margin-bottom:22px;
        font-size:.95rem;
        color:var(--muted);
    }
    .breadcrumb a {
        color:var(--primary);
        text-decoration:none;
    }
    .programme-header {
        display:flex;
        flex-direction:column;
        gap:18px;
    }
    .programme-header h1 {
        margin:0;
        font-size:2.8rem;
        color:var(--text);
        line-height:1.05;
    }
    .programme-description {
        color:var(--muted);
        line-height:1.85;
        margin-top:18px;
    }
    .programme-actions {
        display:flex;
        gap:14px;
        flex-wrap:wrap;
        margin-top:28px;
    }
    .btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:10px;
        padding:14px 22px;
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
    .programme-body {
        margin-top:40px;
        display:grid;
        gap:32px;
    }
    @media (min-width:992px) {
        .programme-body { grid-template-columns:1fr 360px; }
    }
    .programme-panel {
        background:var(--surface);
        border:1px solid var(--border);
        border-radius:24px;
        padding:28px;
        box-shadow:0 24px 60px rgba(17,24,39,0.04);
    }
    .programme-panel h2 {
        margin-top:0;
        margin-bottom:18px;
        font-size:1.4rem;
        color:var(--text);
    }
    .programme-panel ul {
        margin:0;
        padding-left:20px;
        color:var(--muted);
        line-height:1.8;
    }
    .programme-panel li {
        margin-bottom:12px;
    }
</style>

<section class="page-section">
    <div class="breadcrumb">
        <a href="{{ route('programmes.index') }}">Programmes</a> &rsaquo; {{ $category['titre'] }}
    </div>

    <div class="programme-header">
        <h1>{{ $category['titre'] }}</h1>
    </div>

    <div class="programme-actions">
        <a href="{{ route('candidatures.create') }}" class="btn btn-primary">Soumettre une candidature</a>
        <a href="{{ route('programmes.index') }}" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <div class="programme-body">
        <div>
            <h2>Description</h2>
            <p class="programme-description">{{ $category['description'] }}</p>

            @if (isset($category['contenu']['programmes']))
                @foreach ($category['contenu']['programmes'] as $programme)
                    @if (is_array($programme))
                        <div style="margin-top:32px;">
                            <h2 style="margin-bottom:14px;">{{ $programme['nom'] }}</h2>
                        </div>
                    @else
                        <div style="margin-top:24px;">
                            <li>{{ $programme }}</li>
                        </div>
                    @endif
                @endforeach
            @endif

            @if (isset($category['contenu']['details']))
                <div style="margin-top:24px;">
                    <strong>Informations clés</strong>
                    <ul>
                        @foreach ($category['contenu']['details'] as $label => $value)
                            <li><strong>{{ $label }} :</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($category['contenu']['organisation']))
                <div style="margin-top:32px;">
                    <strong>Organisation du cycle</strong>
                    <ul>
                        @foreach ($category['contenu']['organisation'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($category['contenu']['specialites']))
                <div style="margin-top:32px;">
                    <strong>Spécialités</strong>
                    <ul>
                        @foreach ($category['contenu']['specialites'] as $specialite)
                            <li>{{ $specialite }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>

        <aside class="programme-panel">
            <h2>Actions rapides</h2>
            <ul>
                <li><a href="{{ route('candidatures.create') }}" class="btn btn-primary" style="display:block; width:100%; text-align:center;">Soumettre une candidature</a></li>
                <li style="margin-top:12px;"><a href="{{ route('programmes.index') }}" class="btn btn-secondary" style="display:block; width:100%; text-align:center;">Retour à la liste</a></li>
            </ul>
        </aside>
    </div>
</section>
@endsection