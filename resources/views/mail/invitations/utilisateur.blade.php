<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>Invitation au back-office EPF Africa</title>
        <style>
            @media only screen and (max-width: 620px) {
                .email-container {
                    width: 100% !important;
                }

                .email-content {
                    padding: 28px 22px !important;
                }

                .access-table td {
                    display: block !important;
                    width: 100% !important;
                }
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0; background-color: #f6f3fb; color: #260052; font-family: Arial, Helvetica, sans-serif;">
        <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">
            Votre accès au back-office EPF Africa est prêt. Définissez votre mot de passe dans les {{ $dureeValidite }} minutes.
        </div>

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background-color: #f6f3fb;">
            <tr>
                <td align="center" style="padding: 32px 12px;">
                    <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" class="email-container" style="width: 600px; max-width: 600px;">
                        <tr>
                            <td align="center" style="padding: 0 0 22px;">
                                <a href="{{ config('app.url') }}" style="display: inline-block; text-decoration: none;">
                                    <img src="{{ $logoUrl }}" width="96" height="96" alt="EPF Africa" style="display: block; width: 96px; height: 96px; border: 0; border-radius: 18px; object-fit: contain; background-color: #ffffff;">
                                </a>
                                <p style="margin: 14px 0 0; color: #260052; font-size: 20px; font-weight: 700;">EPF Africa</p>
                                <p style="margin: 5px 0 0; color: #706784; font-size: 13px; letter-spacing: 1.5px; text-transform: uppercase;">Back-office des admissions</p>
                            </td>
                        </tr>

                        <tr>
                            <td style="overflow: hidden; border-radius: 24px; background-color: #ffffff; box-shadow: 0 16px 45px rgba(38, 0, 82, 0.12);">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="height: 8px; background: linear-gradient(90deg, #260052 0%, #260052 62%, #e3062f 62%, #e3062f 100%); font-size: 0; line-height: 0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="email-content" style="padding: 42px 46px;">
                                            <p style="margin: 0 0 12px; color: #e3062f; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">Invitation sécurisée</p>
                                            <h1 style="margin: 0; color: #260052; font-size: 30px; line-height: 1.25;">Bienvenue dans le back-office</h1>
                                            <p style="margin: 20px 0 0; color: #4f4663; font-size: 16px; line-height: 1.7;">
                                                Bonjour {{ $nom }}, un accès professionnel à l’espace d’administration EPF Africa vient de vous être créé.
                                            </p>

                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" class="access-table" style="margin-top: 26px; width: 100%; border: 1px solid #e6def1; border-radius: 16px; background-color: #faf8fd;">
                                                <tr>
                                                    <td style="width: 50%; padding: 18px 20px; border-bottom: 1px solid #e6def1;">
                                                        <p style="margin: 0; color: #706784; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Compte</p>
                                                        <p style="margin: 7px 0 0; color: #260052; font-size: 14px; font-weight: 700; word-break: break-word;">{{ $email }}</p>
                                                    </td>
                                                    <td style="width: 50%; padding: 18px 20px; border-bottom: 1px solid #e6def1;">
                                                        <p style="margin: 0; color: #706784; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Rôle attribué</p>
                                                        <p style="margin: 7px 0 0; color: #260052; font-size: 14px; font-weight: 700;">{{ $role }}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="padding: 16px 20px;">
                                                        <p style="margin: 0; color: #4f4663; font-size: 13px; line-height: 1.6;">
                                                            Pour activer ce compte, choisissez personnellement votre mot de passe. L’administrateur n’en connaît aucune version.
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 30px 0 24px;">
                                                <tr>
                                                    <td align="center">
                                                        <a href="{{ $url }}" style="display: inline-block; border-radius: 12px; background-color: #e3062f; color: #ffffff; padding: 15px 26px; font-size: 15px; font-weight: 700; text-decoration: none;">
                                                            Définir mon mot de passe
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; border-radius: 12px; background-color: #fff5f6;">
                                                <tr>
                                                    <td style="padding: 15px 18px;">
                                                        <p style="margin: 0; color: #8a1730; font-size: 13px; line-height: 1.6;">
                                                            <strong>Ce lien expire dans {{ $dureeValidite }} minutes.</strong> S’il expire, demandez au super-administrateur de renvoyer l’invitation.
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                            <p style="margin: 26px 0 0; color: #706784; font-size: 13px; line-height: 1.7;">
                                                Si vous n’attendiez pas cette invitation, ignorez ce message. Ne transférez jamais cet email : son lien est personnel.
                                            </p>

                                            <p style="margin: 26px 0 0; color: #260052; font-size: 14px; line-height: 1.6;">
                                                À bientôt,<br>
                                                <strong>L’équipe EPF Africa</strong>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 22px 20px 0;">
                                <p style="margin: 0; color: #706784; font-size: 11px; line-height: 1.6; text-align: center;">
                                    Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :
                                </p>
                                <p style="margin: 7px 0 0; color: #260052; font-size: 11px; line-height: 1.6; text-align: center; word-break: break-all;">
                                    <a href="{{ $url }}" style="color: #260052;">{{ $url }}</a>
                                </p>
                                <p style="margin: 18px 0 0; color: #8f879e; font-size: 11px; text-align: center;">
                                    © {{ date('Y') }} EPF Africa — Engineering School
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
