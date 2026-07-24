Bonjour {{ $candidature->candidat->prenom }},

Après étude de votre dossier, le jury a réorienté votre candidature de {{ $ancienProgramme->nom }} vers {{ $candidature->programme?->nom ?? 'un autre programme' }}.

Explication du jury :
{{ $motif }}

Consultez votre dossier avec votre lien personnel :
{{ $urlSuivi }}

L’équipe EPF Africa
