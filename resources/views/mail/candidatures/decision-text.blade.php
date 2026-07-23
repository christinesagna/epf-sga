Bonjour {{ $candidature->candidat->prenom }},

Le jury a rendu sa décision pour votre candidature au programme {{ $candidature->programme?->nom ?? 'sélectionné' }}.

Décision : {{ $candidature->statut->libelle() }}

@if ($motif)
Précision du jury :
{{ $motif }}

@endif
Consultez le suivi de votre candidature :
{{ $urlSuivi }}

L’équipe EPF Africa
