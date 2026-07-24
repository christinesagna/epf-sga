<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Réorientation de votre candidature</title>
    </head>
    <body style="margin:0; padding:0; background:#f6f3fb; color:#260052; font-family:Arial,Helvetica,sans-serif;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background:#f6f3fb;">
            <tr>
                <td align="center" style="padding:32px 12px;">
                    <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px; max-width:100%;">
                        <tr>
                            <td align="center" style="padding-bottom:22px;">
                                <img src="{{ $logoUrl }}" width="90" height="90" alt="EPF Africa" style="display:block; width:90px; height:90px; border-radius:18px; object-fit:contain; background:#fff;">
                                <p style="margin:12px 0 0; font-size:20px; font-weight:700;">EPF Africa</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="overflow:hidden; border-radius:24px; background:#fff; box-shadow:0 16px 45px rgba(38,0,82,.12);">
                                <div style="height:8px; background:#e3062f;"></div>
                                <div style="padding:40px 44px;">
                                    <p style="margin:0; color:#e3062f; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Proposition du jury</p>
                                    <h1 style="margin:12px 0 0; font-size:28px; line-height:1.3;">Votre candidature a été réorientée</h1>
                                    <p style="margin:20px 0 0; color:#4f4663; font-size:16px; line-height:1.7;">
                                        Bonjour {{ $candidature->candidat->prenom }}, après étude de votre dossier, le jury a réorienté votre candidature de
                                        <strong>{{ $ancienProgramme->nom }}</strong> vers
                                        <strong>{{ $candidature->programme?->nom ?? 'un autre programme' }}</strong>.
                                    </p>

                                    <div style="margin-top:24px; border-radius:14px; background:#f6f3fb; padding:18px; color:#4f4663; line-height:1.7;">
                                        <strong>Explication du jury :</strong><br>
                                        {{ $motif }}
                                    </div>

                                    <div style="margin-top:30px; text-align:center;">
                                        <a href="{{ $urlSuivi }}" style="display:inline-block; border-radius:12px; background:#e3062f; color:#fff; padding:15px 26px; font-size:15px; font-weight:700; text-decoration:none;">
                                            Consulter mon dossier réorienté
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:20px; color:#706784; font-size:11px; line-height:1.6; text-align:center; word-break:break-all;">
                                Si le bouton ne fonctionne pas :<br>
                                <a href="{{ $urlSuivi }}" style="color:#260052;">{{ $urlSuivi }}</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
