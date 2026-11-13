<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
    <tr>
        <td align="center">
            <table width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px;">

                <tr>
                    <td align="center" style="padding:22px; border-bottom:4px solid #2e7d32;">
                        <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="GéoDéchet" height="55" style="display:block;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px; color:#222222; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 14px 0;">Bonjour {{ $chauffeur->name }},</p>
                        <p style="margin:0 0 14px 0;">Un signalement vous a été affecté :</p>

                        <table width="100%" cellpadding="8" cellspacing="0" style="background-color:#f3f8f3; border-radius:6px; font-size:15px;">
                            <tr>
                                <td width="40%"><strong>Numéro</strong></td>
                                <td>{{ $signalement->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Type de déchet</strong></td>
                                <td>{{ $type }}</td>
                            </tr>
                            <tr>
                                <td><strong>Unité</strong></td>
                                <td>{{ $signalement->unite ?? 'Hors des Unités' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Description</strong></td>
                                <td>{{ $signalement->description ?? 'Aucune' }}</td>
                            </tr>
                        </table>

                        <p style="margin:18px 0 0 0;">Merci de vous rendre sur place.</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:16px; background-color:#f3f4f6; color:#777777; font-size:12px;">
                        GéoDéchet, gestion des déchets urbains, Parcelles Assainies
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
