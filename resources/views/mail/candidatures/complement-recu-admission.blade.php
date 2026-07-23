<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Documents complémentaires reçus</title>
    </head>
    <body style="margin:0; padding:0; background:#f6f3fb; color:#260052; font-family:Arial,Helvetica,sans-serif;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background:#f6f3fb;">
            <tr>
                <td align="center" style="padding:34px 12px;">
                    <table role="presentation" width="580" cellspacing="0" cellpadding="0" border="0" style="width:580px; max-width:100%; overflow:hidden; border-radius:22px; background:#fff; box-shadow:0 16px 45px rgba(38,0,82,.12);">
                        <tr><td style="height:8px; background:#260052;"></td></tr>
                        <tr>
                            <td style="padding:38px 42px;">
                                <p style="margin:0; color:#e3062f; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Service d’admission</p>
                                <h1 style="margin:12px 0 0; font-size:27px;">Complément reçu</h1>
                                <p style="margin:20px 0 0; color:#4f4663; font-size:16px; line-height:1.7;">
                                    {{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}
                                    a transmis les documents complémentaires demandés pour
                                    <strong>{{ $candidature->programme?->nom ?? 'sa candidature' }}</strong>.
                                </p>
                                <div style="margin-top:28px;">
                                    <a href="{{ $urlDossier }}" style="display:inline-block; border-radius:12px; background:#e3062f; color:#fff; padding:14px 24px; font-weight:700; text-decoration:none;">
                                        Reprendre le traitement
                                    </a>
                                </div>
                                <p style="margin:22px 0 0; color:#706784; font-size:13px;">Une connexion au back-office est requise pour consulter le dossier.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
