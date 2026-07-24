<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Lettre d’admission EPF Africa</title>
        <style>
            @page {
                margin: 0;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                color: #260052;
                background: #ffffff;
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                line-height: 1.65;
            }

            .page {
                position: relative;
                height: 251mm;
                padding: 18mm 22mm;
            }

            .top-line {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 8px;
                background: #e3062f;
            }

            .header {
                width: 100%;
                border-bottom: 1px solid #e6def1;
                padding-bottom: 18px;
            }

            .header td {
                vertical-align: middle;
            }

            .logo {
                width: 76px;
                height: 76px;
                object-fit: contain;
            }

            .school {
                text-align: right;
            }

            .school-name {
                margin: 0;
                font-size: 21px;
                font-weight: bold;
            }

            .school-subtitle {
                margin: 5px 0 0;
                color: #706784;
                font-size: 10px;
                letter-spacing: 1.2px;
                text-transform: uppercase;
            }

            .meta {
                margin-top: 14px;
                text-align: right;
                color: #706784;
                font-size: 10px;
            }

            .eyebrow {
                margin-top: 24px;
                color: #e3062f;
                font-size: 10px;
                font-weight: bold;
                letter-spacing: 2px;
                text-transform: uppercase;
            }

            h1 {
                margin: 8px 0 0;
                font-size: 27px;
                line-height: 1.25;
            }

            .recipient {
                margin-top: 20px;
            }

            .recipient strong {
                font-size: 14px;
            }

            .content {
                margin-top: 18px;
                color: #3f3652;
                font-size: 13px;
            }

            .programme {
                margin: 18px 0;
                border-left: 5px solid #e3062f;
                border-radius: 8px;
                background: #f8f5fc;
                padding: 17px 20px;
            }

            .programme-label {
                margin: 0;
                color: #706784;
                font-size: 9px;
                font-weight: bold;
                letter-spacing: 1.4px;
                text-transform: uppercase;
            }

            .programme-name {
                margin: 7px 0 0;
                color: #260052;
                font-size: 17px;
                font-weight: bold;
            }

            .programme-level {
                margin: 4px 0 0;
                color: #706784;
            }

            .signature {
                margin-top: 24px;
                text-align: right;
            }

            .signature-name {
                margin-top: 24px;
                font-weight: bold;
            }

            .footer {
                position: absolute;
                right: 22mm;
                bottom: 6mm;
                left: 22mm;
                border-top: 1px solid #e6def1;
                padding-top: 10px;
                color: #706784;
                font-size: 8px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <main class="page">
            <div class="top-line"></div>

            <table class="header" role="presentation">
                <tr>
                    <td>
                        <img src="{{ $logoDataUri }}" alt="EPF Africa" class="logo">
                    </td>
                    <td class="school">
                        <p class="school-name">EPF Africa</p>
                        <p class="school-subtitle">Engineering School - Creating the future together</p>
                    </td>
                </tr>
            </table>

            <div class="meta">
                Référence : {{ $reference }}<br>
                Émise le {{ $dateDecision->format('d/m/Y') }}
            </div>

            <p class="eyebrow">Décision officielle du jury</p>
            <h1>Lettre d’admission</h1>

            <div class="recipient">
                À l’attention de<br>
                <strong>{{ $candidature->candidat->prenom }} {{ $candidature->candidat->nom }}</strong><br>
                {{ $candidature->candidat->email }}
            </div>

            <div class="content">
                <p>Madame, Monsieur,</p>

                <p>
                    À la suite de l’étude de votre dossier de candidature, nous avons le plaisir de vous informer que
                    le jury d’admission de l’EPF Africa a prononcé votre admission au programme suivant :
                </p>

                <div class="programme">
                    <p class="programme-label">Programme d’admission</p>
                    <p class="programme-name">{{ $candidature->programme?->nom ?? 'Programme EPF Africa' }}</p>
                    <p class="programme-level">
                        Niveau : {{ $candidature->programmeNiveau?->niveau?->libelle ?? 'Non renseigné' }}
                    </p>
                </div>

                <p>
                    Cette admission est prononcée sous réserve de la présentation des documents originaux et du
                    respect des formalités administratives et financières communiquées par l’établissement.
                </p>

                <p>
                    Nous vous félicitons pour votre admission et serons heureux de vous accueillir prochainement
                    au sein de l’EPF Africa.
                </p>

                <p>Veuillez recevoir nos sincères salutations.</p>
            </div>

            <div class="signature">
                <p>Pour l’EPF Africa,</p>
                <p class="signature-name">La Direction des admissions</p>
            </div>

            <footer class="footer">
                EPF Africa - Document généré électroniquement depuis le Système de Gestion des Admissions.<br>
                Vérification : référence {{ $reference }}.
            </footer>
        </main>
    </body>
</html>
