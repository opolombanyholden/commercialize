<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructions - Devis {{ $quote['number'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 16px;
            color: #666;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .step {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
        .step-number {
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .link-box {
            background-color: #f1f3f4;
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        .link {
            color: #007bff;
            font-weight: bold;
            font-size: 14px;
            word-break: break-all;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #28a745;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .client-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .grid {
            display: table;
            width: 100%;
            margin: 10px 0;
        }
        .grid-item {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="logo">CommercialiZE</div>
        <div class="subtitle">Instructions de paiement et d'accès</div>
    </div>

    <!-- Informations du devis -->
    <div class="success-box">
        <h2 style="margin-top: 0; color: #28a745;">✅ Devis généré avec succès</h2>
        <div class="grid">
            <div class="grid-item">
                <strong>Numéro :</strong> {{ $quote['number'] }}<br>
                <strong>Date :</strong> {{ $quote['date']->format('d/m/Y') }}<br>
                <strong>Objet :</strong> {{ $quote['object'] }}
            </div>
            <div class="grid-item">
                <strong>Client :</strong> {{ $client['name'] }}<br>
                <strong>Valide jusqu'au :</strong> {{ $quote['valid_until']->format('d/m/Y') }}<br>
                <strong>Type :</strong> {{ ucfirst($quote['type']) }}
            </div>
        </div>
    </div>

    <!-- Montant total -->
    <div class="amount">
        Montant total du devis : {{ number_format($totals['total'], 0, ',', ' ') }} FCFA
    </div>

    <!-- Étapes à suivre -->
    <h3 style="color: #007bff; margin-top: 30px;">📋 Étapes à suivre :</h3>

    <div class="step">
        <span class="step-number">1</span>
        <strong>{{ $instructions['step1'] }}</strong>
        <div class="link-box" style="margin-top: 10px;">
            <div style="margin-bottom: 5px; font-weight: bold;">🔗 Lien de paiement :</div>
            <div class="link">{{ $important_links['payment'] }}</div>
        </div>
    </div>

    <div class="step">
        <span class="step-number">2</span>
        <strong>{{ $instructions['step2'] }}</strong>
        <div style="margin-top: 8px; font-size: 14px; color: #666;">
            Le mot de passe sera envoyé par email ou SMS selon votre choix lors du paiement.
        </div>
    </div>

    <div class="step">
        <span class="step-number">3</span>
        <strong>{{ $instructions['step3'] }}</strong>
        <div class="link-box" style="margin-top: 10px;">
            <div style="margin-bottom: 5px; font-weight: bold;">🔐 Lien d'accès au devis :</div>
            <div class="link">{{ $important_links['access'] }}</div>
        </div>
    </div>

    <!-- Informations importantes -->
    <div class="warning-box">
        <h4 style="margin-top: 0; color: #856404;">⚠️ Informations importantes :</h4>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Conservez ce document jusqu'au téléchargement final du devis</li>
            <li>Le mot de passe sera envoyé uniquement après confirmation du paiement</li>
            <li>Le lien d'accès au devis est sécurisé et nécessite le mot de passe</li>
            <li>Ce devis est valide jusqu'au {{ $quote['valid_until']->format('d/m/Y') }}</li>
        </ul>
    </div>

    <!-- Récapitulatif -->
    <div class="info-box">
        <h4 style="margin-top: 0; color: #495057;">📄 Récapitulatif :</h4>
        <div class="grid">
            <div class="grid-item">
                <strong>Entreprise :</strong><br>
                {{ $company['name'] }}<br>
                @if($company['address'])
                    {{ $company['address'] }}<br>
                @endif
                @if($company['phone'])
                    Tél : {{ $company['phone'] }}<br>
                @endif
                Email : {{ $company['email'] }}
            </div>
            <div class="grid-item">
                <strong>Client :</strong><br>
                {{ $client['name'] }}<br>
                @if($client['address'])
                    {{ $client['address'] }}<br>
                @endif
                @if($client['city'])
                    {{ $client['city'] }}<br>
                @endif
                @if($client['email'])
                    Email : {{ $client['email'] }}<br>
                @endif
                @if($client['phone'])
                    Tél : {{ $client['phone'] }}
                @endif
            </div>
        </div>
    </div>

    <!-- Support -->
    <div class="info-box">
        <h4 style="margin-top: 0; color: #007bff;">💬 Besoin d'aide ?</h4>
        <p style="margin: 5px 0;">
            Si vous rencontrez des difficultés, contactez-nous :<br>
            📧 Email : {{ $company['email'] }}<br>
            @if($company['phone'])
                📞 Téléphone : {{ $company['phone'] }}
            @endif
        </p>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            Document généré le {{ $generated_at->format('d/m/Y à H:i') }}<br>
            CommercialiZE - Système de gestion de devis sécurisé<br>
            <strong>ID du document :</strong> {{ $pdf_id }}
        </p>
    </div>
</body>
</html>