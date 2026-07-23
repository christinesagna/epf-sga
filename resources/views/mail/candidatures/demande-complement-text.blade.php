Bonjour {{ $candidature->candidat->prenom }},

Le service d’admission demande un complément pour votre candidature à {{ $candidature->programme?->nom ?? 'EPF Africa' }}.

Motif :
{{ $motif }}

Consultez et complétez votre dossier avec votre lien personnel :
{{ $urlSuivi }}

L’équipe EPF Africa
