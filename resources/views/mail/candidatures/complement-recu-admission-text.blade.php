Bonjour,

{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }} a transmis les documents complémentaires demandés pour {{ $candidature->programme?->nom ?? 'sa candidature' }}.

Reprendre le traitement :
{{ $urlDossier }}

Une connexion au back-office est requise.
