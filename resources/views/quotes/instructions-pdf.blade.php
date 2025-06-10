<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Instructions de paiement - Devis {{ $quote['number'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #E91E63;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #E91E63;
        }
        .brand-z { color: #8BC34A; }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
        }
        .payment-box {
            background-color: #f0f8ff;
            border: 2px solid #E91E63;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #E91E63;
        }
        .password-box {
            background-color: #e8f5e8;
            border: 2px dashed #8BC34A;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .steps {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .step {
            margin: 15px 0;
            padding: 10px;
            border-left: 3px solid #E91E63;
            padding-left: 15px;
        }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table td { padding: 8px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <span>Commerciali</span><span class="brand-z">Ze</span>
        </div>
        <h2>Instructions de paiement</h2>
        <p>Devis {{ $quote['number'] }} - {{ $quote['date']->format('d/m/Y') }}</p>
    </div>

    <div class="alert">
        <strong>🔒 Votre devis est prêt !</strong><br>
        Pour des raisons de sécurité, le PDF final de votre devis est protégé par un mot de passe.
        Vous recevrez ce mot de passe après confirmation de votre paiement.
    </div>

    <div class="payment-box">
        <h3>💰 MONTANT À RÉGLER</h3>
        <div class="amount">{{ number_format($totals['total'], 0, ',', ' ') }} FCFA</div>
        <p><em>{{ $totals['total_words'] }}</em></p>
    </div>

    <div class="info-box">
        <h4>📋 Récapitulatif du devis</h4>
        <table>
            <tr>
                <td class="label">Numéro :</td>
                <td>{{ $quote['number'] }}</td>
            </tr>
            <tr>
                <td class="label">Objet :</td>
                <td>{{ $quote['object'] }}</td>
            </tr>
            <tr>
                <td class="label">Client :</td>
                <td>{{ $client['name'] }}</td>
            </tr>
            <tr>
                <td class="label">Sous-total HT :</td>
                <td>{{ number_format($totals['subtotal'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="label">Taxes :</td>
                <td>{{ number_format($totals['total_tax'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="label"><strong>Total TTC :</strong></td>
                <td><strong>{{ number_format($totals['total'], 0, ',', ' ') }} FCFA</strong></td>
            </tr>
        </table>
    </div>

    <div class="steps">
        <h4>🚀 Étapes pour obtenir votre devis final :</h4>
        
        <div class="step">
            <strong>1. Effectuez le paiement</strong><br>
            Réglez le montant de {{ number_format($totals['total'], 0, ',', ' ') }} FCFA selon les modalités convenues :
            <ul>
                <li>Mobile Money (Orange Money, Moov Money)</li>
                <li>Virement bancaire</li>
                <li>Espèces (en agence)</li>
            </ul>
        </div>

        <div class="step">
            <strong>2. Conservez votre référence de paiement</strong><br>
            Notez précieusement la référence/reçu de votre transaction.
        </div>

        <div class="step">
            <strong>3. Contactez-nous pour validation</strong><br>
            Envoyez-nous votre référence de paiement pour validation.
        </div>

        <div class="step">
            <strong>4. Recevez votre mot de passe</strong><br>
            Une fois le paiement vérifié, vous recevrez le mot de passe par email ou SMS.
        </div>

        <div class="step">
            <strong>5. Téléchargez votre devis</strong><br>
            Utilisez le lien ci-dessous avec votre mot de passe pour télécharger le PDF final.
        </div>
    </div>

    <div class="password-box">
        <h4>🔑 Accès au devis final</h4>
        <p><strong>Lien de téléchargement :</strong></p>
        <p style="font-family: monospace; background: #f0f0f0; padding: 10px; border-radius: 5px;">
            {{ $access_url }}
        </p>
        <p><em>Vous devrez saisir le mot de passe reçu après paiement</em></p>
    </div>

    <div style="margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
        <p><strong>{{ $company['name'] }}</strong></p>
        @if($company['phone'])
            <p>📞 {{ $company['phone'] }}</p>
        @endif
        @if($company['email'])
            <p>📧 {{ $company['email'] }}</p>
        @endif
        <p style="font-size: 10px; color: #666; margin-top: 15px;">
            Document généré le {{ $generated_at->format('d/m/Y à H:i') }} avec CommercialiZe Light
        </p>
    </div>
</body>
</html>