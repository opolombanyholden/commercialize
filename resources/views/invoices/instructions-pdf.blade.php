<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructions - Facture {{ $invoice->formatted_invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #dc3545;
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
        .danger-box {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-left: 4px solid #dc3545;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .step {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        .step-number {
            background-color: #dc3545;
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
            border: 2px dashed #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        .link {
            color: #dc3545;
            font-weight: bold;
            font-size: 14px;
            word-break: break-all;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #dc3545;
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
        .payment-methods {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .method-item {
            padding: 8px 0;
            border-bottom: 1px solid #e1e1e1;
        }
        .method-item:last-child {
            border-bottom: none;
        }
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="logo">CommercialiZE</div>
        <div class="subtitle">Instructions de paiement et d'accès - Facture</div>
    </div>

    <!-- Informations de la facture -->
    <div class="success-box">
        <h2 style="margin-top: 0; color: #28a745;">✅ Facture générée avec succès</h2>
        <div class="grid">
            <div class="grid-item">
                <strong>Numéro :</strong> {{ $invoice->formatted_invoice_number }}<br>
                <strong>Date :</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
                <strong>Client :</strong> {{ $invoice->client_name }}
            </div>
            <div class="grid-item">
                <strong>Échéance :</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                <strong>Statut :</strong> {{ $invoice->status_label }}<br>
                @if($invoice->isOverdue())
                    <strong style="color: #dc3545;">⚠ Retard :</strong> {{ abs($invoice->days_until_due) }} jour(s)
                @endif
            </div>
        </div>
    </div>

    <!-- Alerte si facture en retard -->
    @if($invoice->isOverdue())
        <div class="danger-box">
            <h4 style="margin-top: 0; color: #721c24;">🚨 FACTURE EN RETARD</h4>
            <p style="margin: 5px 0;">
                Cette facture a dépassé sa date d'échéance de <strong>{{ abs($invoice->days_until_due) }} jour(s)</strong>.<br>
                Veuillez procéder au paiement dans les plus brefs délais pour éviter des pénalités.
            </p>
        </div>
    @endif

    <!-- Montant total -->
    <div class="amount">
        Montant total de la facture : {{ $invoice->formatted_total }}
    </div>

    <!-- Étapes à suivre -->
    <h3 style="color: #dc3545; margin-top: 30px;">📋 Étapes à suivre :</h3>

    <div class="step">
        <span class="step-number">1</span>
        <strong>Accédez à la page de paiement sécurisée</strong>
        <div class="link-box" style="margin-top: 10px;">
            <div style="margin-bottom: 5px; font-weight: bold;">🔗 Lien de paiement :</div>
            <div class="link">{{ route('invoices.payments') }}</div>
        </div>
    </div>

    <div class="step">
        <span class="step-number">2</span>
        <strong>Effectuez le paiement selon la méthode de votre choix</strong>
        <div class="payment-methods" style="margin-top: 10px;">
            <h5 style="margin: 0 0 10px 0; color: #1976d2;">💳 Méthodes de paiement acceptées :</h5>
            <div class="method-item">📱 <strong>Mobile Money :</strong> Orange, Moov, Airtel Money</div>
            <div class="method-item">🏦 <strong>Virement bancaire :</strong> Coordonnées fournies lors du paiement</div>
            <div class="method-item">💳 <strong>PayPal :</strong> Paiement en ligne sécurisé</div>
            <div class="method-item">💎 <strong>Stripe :</strong> Carte bancaire internationale</div>
        </div>
    </div>

    <div class="step">
        <span class="step-number">3</span>
        <strong>Recevez votre mot de passe d'accès</strong>
        <div style="margin-top: 8px; font-size: 14px; color: #666;">
            Après confirmation du paiement, vous recevrez un mot de passe par email ou SMS selon votre choix.
        </div>
    </div>

    <div class="step">
        <span class="step-number">4</span>
        <strong>Téléchargez votre facture PDF sécurisée</strong>
        <div class="link-box" style="margin-top: 10px;">
            <div style="margin-bottom: 5px; font-weight: bold;">🔐 Lien d'accès à la facture :</div>
            <div class="link">{{ route('invoices.password-form', $invoice->id) }}</div>
        </div>
    </div>

    <!-- Informations importantes -->
    <div class="warning-box">
        <h4 style="margin-top: 0; color: #856404;">⚠️ Informations importantes :</h4>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Conservez ce document jusqu'au téléchargement final de la facture</li>
            <li>Le mot de passe sera envoyé uniquement après confirmation du paiement</li>
            <li>Le lien d'accès à la facture est sécurisé et nécessite le mot de passe</li>
            @if($invoice->isOverdue())
                <li style="color: #dc3545;"><strong>Cette facture est en retard de {{ abs($invoice->days_until_due) }} jour(s)</strong></li>
            @else
                <li>Date limite de paiement : {{ $invoice->due_date->format('d/m/Y') }}</li>
            @endif
            <li>Toute question concernant cette facture doit mentionner le numéro {{ $invoice->formatted_invoice_number }}</li>
        </ul>
    </div>

    <!-- Récapitulatif des montants -->
    <div class="info-box">
        <h4 style="margin-top: 0; color: #495057;">💰 Détail des montants :</h4>
        <div style="padding: 10px 0;">
            <div style="display: table; width: 100%; margin-bottom: 5px;">
                <div style="display: table-cell; width: 60%;">Sous-total HT :</div>
                <div style="display: table-cell; width: 40%; text-align: right; font-weight: bold;">{{ $invoice->formatted_subtotal }}</div>
            </div>
            @if($invoice->total_tax > 0)
                <div style="display: table; width: 100%; margin-bottom: 5px;">
                    <div style="display: table-cell; width: 60%;">Total des taxes :</div>
                    <div style="display: table-cell; width: 40%; text-align: right; font-weight: bold;">{{ $invoice->formatted_total_tax }}</div>
                </div>
            @endif
            <div style="display: table; width: 100%; border-top: 2px solid #dc3545; padding-top: 5px; margin-top: 10px;">
                <div style="display: table-cell; width: 60%; font-size: 16px; font-weight: bold; color: #dc3545;">TOTAL TTC :</div>
                <div style="display: table-cell; width: 40%; text-align: right; font-size: 16px; font-weight: bold; color: #dc3545;">{{ $invoice->formatted_total }}</div>
            </div>
        </div>
    </div>

    <!-- Récapitulatif des contacts -->
    <div class="info-box">
        <h4 style="margin-top: 0; color: #495057;">📄 Informations de contact :</h4>
        <div class="grid">
            <div class="grid-item">
                <strong>Émetteur :</strong><br>
                {{ auth()->user()->company_name ?? 'Votre Entreprise' }}<br>
                @if(auth()->user()->company_address)
                    {{ auth()->user()->company_address }}<br>
                @endif
                @if(auth()->user()->phone)
                    Tél : {{ auth()->user()->phone }}<br>
                @endif
                Email : {{ auth()->user()->email }}
            </div>
            <div class="grid-item">
                <strong>Facturé à :</strong><br>
                {{ $invoice->client_name }}<br>
                @if($invoice->client_email)
                    Email : {{ $invoice->client_email }}<br>
                @endif
                @if($invoice->client_phone)
                    Tél : {{ $invoice->client_phone }}<br>
                @endif
                {{ $invoice->client_address }}
            </div>
        </div>
    </div>

    <!-- Conditions générales -->
    <div class="info-box">
        <h4 style="margin-top: 0; color: #dc3545;">📋 Conditions de paiement :</h4>
        <ul style="margin: 10px 0; padding-left: 20px; font-size: 12px;">
            <li>Paiement à réception de facture</li>
            <li>Aucun escompte accordé en cas de paiement anticipé</li>
            <li>Toute facture non réglée à l'échéance pourra donner lieu à des pénalités de retard</li>
            <li>En cas de retard de paiement, une indemnité forfaitaire de 40€ sera due</li>
            <li>TVA non applicable selon l'article 293 B du CGI (si applicable)</li>
        </ul>
    </div>

    <!-- Support -->
    <div class="info-box">
        <h4 style="margin-top: 0; color: #007bff;">💬 Besoin d'aide ?</h4>
        <p style="margin: 5px 0;">
            Si vous rencontrez des difficultés pour le paiement ou l'accès à votre facture :<br>
            📧 Email : {{ auth()->user()->email }}<br>
            @if(auth()->user()->phone)
                📞 Téléphone : {{ auth()->user()->phone }}<br>
            @endif
            💬 Mentionnez toujours le numéro de facture : <strong>{{ $invoice->formatted_invoice_number }}</strong>
        </p>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            Document généré le {{ now()->format('d/m/Y à H:i') }}<br>
            CommercialiZE - Système de gestion de factures sécurisé<br>
            <strong>Référence :</strong> {{ $invoice->id }} | <strong>Version :</strong> Light
        </p>
    </div>
</body>
</html>