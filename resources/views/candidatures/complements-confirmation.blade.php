@extends('layouts.app')

@section('title', 'Complément transmis | SGA EPF')

@section('content')
    <section style="background:linear-gradient(135deg,#fdf1f6 0%,#f8fafc 50%,#eef2ff 100%); min-height:calc(100vh - 72px); padding:48px 16px;">
        <div style="max-width:700px; margin:0 auto; background:#fff; border:1px solid #bbf7d0; border-radius:24px; padding:40px; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,.08);">
            <div style="width:72px; height:72px; margin:0 auto; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#dcfce7; color:#166534; font-size:2rem;">✓</div>
            <h1 style="margin-top:22px; color:#260052; font-size:2rem;">Complément transmis</h1>
            <p style="margin-top:12px; color:#64748b; line-height:1.7;">
                Vos nouveaux documents ont été enregistrés. Votre dossier va être réétudié par le service concerné.
            </p>
            <a href="{{ route('programmes.index') }}"
               style="display:inline-flex; margin-top:28px; border-radius:14px; background:#e3062f; color:#fff; padding:13px 22px; font-weight:700;">
                Retour aux programmes
            </a>
        </div>
    </section>
@endsection
