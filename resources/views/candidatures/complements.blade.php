@extends('layouts.app')

@section('title', 'Complément de candidature | SGA EPF')

@section('content')
    <section style="background:linear-gradient(135deg,#fdf1f6 0%,#f8fafc 50%,#eef2ff 100%); min-height:calc(100vh - 72px); padding:48px 16px;">
        <div style="max-width:900px; margin:0 auto;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:24px; padding:32px; box-shadow:0 20px 50px rgba(15,23,42,.08);">
                <p style="color:#9d174d; font-size:.75rem; font-weight:700; letter-spacing:.15em; text-transform:uppercase;">Complément de dossier</p>
                <h1 style="margin-top:8px; color:#260052; font-size:2rem; font-weight:700;">{{ $candidature->programme->nom }}</h1>
                <p style="margin-top:8px; color:#64748b;">
                    Niveau demandé : {{ $candidature->programmeNiveau->niveau->libelle }}.
                    Déposez uniquement les documents demandés ou corrigés.
                </p>

                @if ($demandeComplement?->commentaire)
                    <div style="margin-top:22px; border:1px solid #fed7aa; background:#fff7ed; color:#9a3412; border-radius:14px; padding:16px; line-height:1.7;">
                        <strong>Message du service d’admission :</strong><br>
                        {{ $demandeComplement->commentaire }}
                    </div>
                @endif

                @if (session('success'))
                    <div style="margin-top:24px; border:1px solid #bbf7d0; background:#f0fdf4; color:#166534; border-radius:14px; padding:16px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div style="margin-top:24px; border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; border-radius:14px; padding:16px;">
                        <ul style="margin:0; padding-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('candidatures.complements.update', [$candidature, $token]) }}"
                      enctype="multipart/form-data"
                      style="margin-top:28px;">
                    @csrf

                    <div style="display:grid; gap:18px;">
                        @foreach ($documentsRequis as $typeDocument)
                            @php($documentActuel = $documentsActuels->get($typeDocument->id))
                            <div style="border:1px solid #e2e8f0; border-radius:18px; padding:20px;">
                                <label for="document_{{ $typeDocument->code }}" style="display:block; color:#334155; font-weight:700;">
                                    {{ $typeDocument->libelle }}
                                </label>

                                @if ($documentActuel)
                                    <p style="margin-top:6px; color:#64748b; font-size:.875rem;">
                                        Document actuel : {{ $documentActuel->original_name }}
                                        — statut : {{ str($documentActuel->statut_validation->value)->replace('_', ' ')->ucfirst() }}
                                    </p>
                                    @if ($documentActuel->commentaire_validation)
                                        <p style="margin-top:6px; color:#b45309; font-size:.875rem;">
                                            Commentaire : {{ $documentActuel->commentaire_validation }}
                                        </p>
                                    @endif
                                @endif

                                <input id="document_{{ $typeDocument->code }}"
                                       name="documents[{{ $typeDocument->code }}]"
                                       type="file"
                                       required
                                       accept="{{ collect($typeDocument->extensions_autorisees)->map(fn ($extension) => '.'.$extension)->implode(',') }}"
                                       style="display:block; width:100%; margin-top:12px;">
                                <p style="margin-top:6px; color:#64748b; font-size:.75rem;">
                                    Formats : {{ implode(', ', $typeDocument->extensions_autorisees) }}
                                    — maximum {{ $typeDocument->taille_max_mb }} Mo
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit"
                            style="margin-top:28px; border:0; border-radius:14px; background:#e3062f; color:#fff; padding:13px 22px; font-weight:700; cursor:pointer;">
                        Transmettre les compléments
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
