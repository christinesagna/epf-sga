<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>Votre candidature EPF Africa</title>
    </head>
    <body style="margin:0; padding:0; background-color:#f6f3fb; color:#260052; font-family:Arial,Helvetica,sans-serif;">
        <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
            Votre candidature a bien été soumise. Conservez ce lien personnel pour suivre son évolution.
        </div>

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#f6f3fb;">
            <tr>
                <td align="center" style="padding:32px 12px;">
                    <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px; max-width:100%;">
                        <tr>
                            <td align="center" style="padding:0 0 22px;">
                                <img src="{{ $logoUrl }}" width="96" height="96" alt="EPF Africa" style="display:block; width:96px; height:96px; border:0; border-radius:18px; object-fit:contain; background-color:#ffffff;">
                                <p style="margin:14px 0 0; font-size:20px; font-weight:700;">EPF Africa</p>
                                <p style="margin:5px 0 0; color:#706784; font-size:12px; letter-spacing:1.5px; text-transform:uppercase;">Système de gestion des admissions</p>
                            </td>
                        </tr>

                        <tr>
                            <td style="overflow:hidden; border-radius:24px; background-color:#ffffff; box-shadow:0 16px 45px rgba(38,0,82,.12);">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="height:8px; background-color:#260052; font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:42px 46px;">
                                            <p style="margin:0 0 12px; color:#e3062f; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Candidature soumise</p>
                                            <h1 style="margin:0; color:#260052; font-size:29px; line-height:1.25;">Votre dossier est enregistré</h1>
                                            <p style="margin:20px 0 0; color:#4f4663; font-size:16px; line-height:1.7;">
                                                Bonjour {{ $candidature->candidat->prenom }}, votre candidature pour
                                                <strong>{{ $candidature->programme?->nom ?? 'le programme sélectionné' }}</strong>
                                                a bien été transmise à EPF Africa.
                                            </p>

                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:26px; width:100%; border:1px solid #e6def1; border-radius:16px; background-color:#faf8fd;">
                                                <tr>
                                                    <td style="padding:18px 20px;">
                                                        <p style="margin:0; color:#706784; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">Formation demandée</p>
                                                        <p style="margin:7px 0 0; color:#260052; font-size:14px; font-weight:700;">{{ $candidature->programme?->nom ?? 'Non renseignée' }}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0 20px 18px;">
                                                        <p style="margin:0; color:#706784; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">Niveau</p>
                                                        <p style="margin:7px 0 0; color:#260052; font-size:14px; font-weight:700;">{{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Non renseigné' }}</p>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:30px 0 24px;">
                                                <tr>
                                                    <td align="center">
                                                        <a href="{{ $urlSuivi }}" style="display:inline-block; border-radius:12px; background-color:#e3062f; color:#ffffff; padding:15px 26px; font-size:15px; font-weight:700; text-decoration:none;">
                                                            Suivre ma candidature
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>

                                            <p style="margin:0; border-radius:12px; background-color:#fff5f6; padding:15px 18px; color:#8a1730; font-size:13px; line-height:1.6;">
                                                <strong>Ce lien est personnel.</strong> Conservez cet e-mail et ne transférez pas son lien : il donne accès au suivi de votre candidature.
                                            </p>

                                            <p style="margin:26px 0 0; color:#260052; font-size:14px; line-height:1.6;">
                                                À bientôt,<br>
                                                <strong>L’équipe EPF Africa</strong>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:22px 20px 0;">
                                <p style="margin:0; color:#706784; font-size:11px; line-height:1.6; text-align:center;">
                                    Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :
                                </p>
                                <p style="margin:7px 0 0; font-size:11px; line-height:1.6; text-align:center; word-break:break-all;">
                                    <a href="{{ $urlSuivi }}" style="color:#260052;">{{ $urlSuivi }}</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
