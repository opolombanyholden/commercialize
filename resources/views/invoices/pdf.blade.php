<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $protectedInvoice->formatted_invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header avec logo */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 10px;
        }
        
        .logo .brand-z {
            color: #8BC34A;
        }
        
        .company-info {
            color: #666;
            line-height: 1.6;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .invoice-info table {
            width: 100%;
        }
        
        .invoice-info td {
            padding: 5px 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .invoice-info td:first-child {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        
        /* Statut */
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        /* Section client */
        .client-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .client-info {
            display: table-cell;
            width: 50%;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .invoice-status {
            display: table-cell;
            width: 50%;
            padding-left: 20px;
            vertical-align: top;
        }
        
        .client-title {
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 15px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 5px;
        }
        
        .client-details {
            line-height: 1.8;
        }
        
        .client-name {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
        }
        
        /* Objet de la facture */
        .invoice-object {
            margin-bottom: 25px;
            text-align: center;
        }
        
        .invoice-object h3 {
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 8px;
        }
        
        .invoice-object-content {
            font-size: 14px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }
        
        /* Tableau des articles */
        .items-section {
            margin: 30px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 15px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: linear-gradient(135deg, #dc3545, #2196F3);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .type-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        
        .type-produit {
            background-color: #FF9800;
        }
        
        .type-service {
            background-color: #2196F3;
        }
        
        /* Section totaux */
        .totals-section {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .totals-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .totals-table .total-label {
            font-weight: bold;
            color: #555;
        }
        
        .totals-table .total-amount {
            text-align: right;
            font-weight: bold;
        }
        
        .totals-table .subtotal-row {
            background-color: #e3f2fd;
        }
        
        .totals-table .final-total {
            background: linear-gradient(135deg, #dc3545, #2196F3);
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        
        /* Informations de paiement */
        .payment-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            border-radius: 0 8px 8px 0;
        }
        
        .payment-info h4 {
            color: #155724;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .overdue-warning {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }
        
        /* Notes */
        .notes-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f0f4f8;
            border-radius: 8px;
            border-left: 4px solid #8BC34A;
        }
        
        .notes-title {
            font-size: 14px;
            font-weight: bold;
            color: #8BC34A;
            margin-bottom: 10px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .footer .generation-info {
            margin-top: 10px;
            font-style: italic;
        }
        
        .currency {
            font-weight: bold;
            color: #2e7d32;
        }
        
        .success-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        /* Taxes détail */
        .taxes-detail {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff3e0;
            border-left: 4px solid #FF9800;
            border-radius: 0 8px 8px 0;
        }
        
        .taxes-detail h4 {
            color: #FF9800;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .tax-breakdown {
            font-size: 11px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header avec logo et informations entreprise -->
        <div class="header">
            <div class="header-left">
                <div class="logo">
                    <span>Commerciali</span><span class="brand-z">Ze</span>
                </div>
                <div class="company-info">
                    <strong>{{ $protectedInvoice->invoice_data['company']['name'] ?? 'CommercialiZe' }}</strong><br>
                    @if(isset($protectedInvoice->invoice_data['company']['address']) && $protectedInvoice->invoice_data['company']['address'])
                        {{ $protectedInvoice->invoice_data['company']['address'] }}<br>
                    @endif
                    @if(isset($protectedInvoice->invoice_data['company']['phone']) && $protectedInvoice->invoice_data['company']['phone'])
                        Tél: {{ $protectedInvoice->invoice_data['company']['phone'] }}<br>
                    @endif
                    Email: {{ $protectedInvoice->invoice_data['company']['email'] ?? 'contact@commercialize.com' }}
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-info">
                    <table>
                        <tr>
                            <td>Numéro :</td>
                            <td><strong>{{ $protectedInvoice->formatted_invoice_number }}</strong></td>
                        </tr>
                        <tr>
                            <td>Date :</td>
                            <td>{{ \Carbon\Carbon::parse($protectedInvoice->invoice_data['invoice']['date'])->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Échéance :</td>
                            <td>{{ $protectedInvoice->due_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Type :</td>
                            <td>
                                <span style="background-color: #fff3cd; padding: 2px 6px; border-radius: 4px;">
                                    {{ ucfirst($protectedInvoice->invoice_data['invoice']['type'] ?? 'Facture') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Message de statut -->
        @if($protectedInvoice->is_paid)
            <div class="success-box">
                <div style="font-size: 16px; font-weight: bold; color: #155724; margin-bottom: 5px;">
                    ✅ FACTURE PAYÉE AVEC SUCCÈS
                </div>
                <div style="color: #155724;">
                    Cette facture a été réglée le {{ $protectedInvoice->paid_at->format('d/m/Y à H:i') }}
                </div>
            </div>
        @elseif($protectedInvoice->isOverdue())
            <div class="overdue-warning">
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">
                    🚨 FACTURE EN RETARD
                </div>
                <div>
                    Cette facture a dépassé sa date d'échéance de {{ abs($protectedInvoice->days_until_due) }} jour(s).
                </div>
            </div>
        @endif

        <!-- Objet de la facture -->
        @if(isset($protectedInvoice->invoice_data['invoice']['object']))
            <div class="invoice-object">
                <h3>OBJET DE LA FACTURE</h3>
                <div class="invoice-object-content">
                    {{ $protectedInvoice->invoice_data['invoice']['object'] }}
                </div>
            </div>
        @endif

        <!-- Informations client -->
        <div class="client-section">
            <div class="client-info">
                <div class="client-title">INFORMATIONS CLIENT</div>
                <div class="client-details">
                    <div class="client-name">{{ $protectedInvoice->invoice_data['client']['name'] }}</div>
                    @if($protectedInvoice->invoice_data['client']['email'])
                        <div>Email: {{ $protectedInvoice->invoice_data['client']['email'] }}</div>
                    @endif
                    @if($protectedInvoice->invoice_data['client']['phone'])
                        <div>Téléphone: {{ $protectedInvoice->invoice_data['client']['phone'] }}</div>
                    @endif
                    @if($protectedInvoice->invoice_data['client']['address'])
                        <div>Adresse: {{ $protectedInvoice->invoice_data['client']['address'] }}</div>
                    @endif
                    @if($protectedInvoice->invoice_data['client']['city'])
                        <div>Ville: {{ $protectedInvoice->invoice_data['client']['city'] }}</div>
                    @endif
                </div>
            </div>
            <div class="invoice-status">
                <div class="client-title">STATUT DE LA FACTURE</div>
                <div>
                    <span class="status-badge {{ $protectedInvoice->status_class }}">
                        {{ $protectedInvoice->status_label }}
                    </span>
                </div>
                @if($protectedInvoice->is_paid)
                    <div style="margin-top: 15px; color: #155724;">
                        <strong>Payée le :</strong><br>
                        {{ $protectedInvoice->paid_at->format('d/m/Y à H:i') }}
                    </div>
                @elseif($protectedInvoice->isOverdue())
                    <div style="margin-top: 15px; color: #721c24;">
                        <strong>En retard de :</strong><br>
                        {{ abs($protectedInvoice->days_until_due) }} jour(s)
                    </div>
                @else
                    <div style="margin-top: 15px; color: #856404;">
                        <strong>À payer avant le :</strong><br>
                        {{ $protectedInvoice->due_date->format('d/m/Y') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Articles de la facture -->
        @if(isset($protectedInvoice->invoice_data['items']) && count($protectedInvoice->invoice_data['items']) > 0)
            <div class="items-section">
                <div class="section-title">DÉTAIL DES ARTICLES</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Description</th>
                            <th style="width: 12%;" class="text-center">Type</th>
                            <th style="width: 12%;" class="text-center">Quantité</th>
                            <th style="width: 18%;" class="text-right">Prix unitaire</th>
                            <th style="width: 18%;" class="text-right">Total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($protectedInvoice->invoice_data['items'] as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['description'] }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="type-badge type-{{ $item['type'] }}">
                                        {{ ucfirst($item['type']) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{ number_format($item['quantity'], 2, ',', ' ') }}
                                </td>
                                <td class="text-right">
                                    <span class="currency">{{ number_format($item['unit_price'], 0, ',', ' ') }} FCFA</span>
                                </td>
                                <td class="text-right">
                                    <span class="currency">{{ number_format($item['line_total'], 0, ',', ' ') }} FCFA</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Détail des taxes appliquées -->
        @if(isset($protectedInvoice->invoice_data['taxes']) && count($protectedInvoice->invoice_data['taxes']) > 0)
            <div class="taxes-detail">
                <h4>📊 DÉTAIL DES TAXES APPLIQUÉES</h4>
                <div class="tax-breakdown">
                    @foreach($protectedInvoice->invoice_data['taxes'] as $tax)
                        <div style="margin-bottom: 5px;">
                            <strong>{{ $tax['name'] }}</strong> ({{ $tax['rate'] }}%) 
                            appliquée sur 
                            @if($tax['apply_on'] === 'total')
                                le total HT de la facture
                            @elseif($tax['apply_on'] === 'products')
                                les produits uniquement
                            @elseif($tax['apply_on'] === 'services')
                                les services uniquement
                            @endif
                            : {{ number_format($tax['base'], 0, ',', ' ') }} FCFA
                            → <strong>{{ number_format($tax['amount'], 0, ',', ' ') }} FCFA</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section totaux -->
        <div class="totals-section">
            <div class="totals-left">
                <!-- Informations de paiement si payée -->
                @if($protectedInvoice->is_paid)
                    <div class="payment-info">
                        <h4>💳 INFORMATIONS DE PAIEMENT</h4>
                        <div style="line-height: 1.6; color: #155724;">
                            @if($protectedInvoice->payment_method)
                                <div><strong>Méthode :</strong> {{ ucfirst(str_replace('_', ' ', $protectedInvoice->payment_method)) }}</div>
                            @endif
                            @if($protectedInvoice->payment_reference)
                                <div><strong>Référence :</strong> {{ $protectedInvoice->payment_reference }}</div>
                            @endif
                            <div><strong>Date de paiement :</strong> {{ $protectedInvoice->paid_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="totals-right">
                <table class="totals-table">
                    @if(isset($protectedInvoice->invoice_data['totals']['subtotal_products']) && $protectedInvoice->invoice_data['totals']['subtotal_products'] > 0)
                        <tr>
                            <td class="total-label">Total Produits HT :</td>
                            <td class="total-amount currency">{{ number_format($protectedInvoice->invoice_data['totals']['subtotal_products'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    @if(isset($protectedInvoice->invoice_data['totals']['subtotal_services']) && $protectedInvoice->invoice_data['totals']['subtotal_services'] > 0)
                        <tr>
                            <td class="total-label">Total Services HT :</td>
                            <td class="total-amount currency">{{ number_format($protectedInvoice->invoice_data['totals']['subtotal_services'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr class="subtotal-row">
                        <td class="total-label">Sous-total HT :</td>
                        <td class="total-amount currency">{{ $protectedInvoice->formatted_subtotal }}</td>
                    </tr>
                    @if($protectedInvoice->total_tax > 0)
                        <tr>
                            <td class="total-label">Total des taxes :</td>
                            <td class="total-amount currency">{{ $protectedInvoice->formatted_total_tax }}</td>
                        </tr>
                    @endif
                    <tr class="final-total">
                        <td class="total-label">TOTAL TTC :</td>
                        <td class="total-amount">{{ $protectedInvoice->formatted_total }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes et conditions -->
        @if(isset($protectedInvoice->invoice_data['invoice']['notes']) && $protectedInvoice->invoice_data['invoice']['notes'])
            <div class="notes-section">
                <div class="notes-title">📋 NOTES ET CONDITIONS DE PAIEMENT</div>
                <div style="line-height: 1.6; color: #333;">
                    {{ $protectedInvoice->invoice_data['invoice']['notes'] }}
                </div>
            </div>
        @endif

        <!-- Message de remerciement -->
        @if($protectedInvoice->is_paid)
            <div style="margin: 30px 0; padding: 20px; background-color: #f0f8ff; border: 2px solid #dc3545; border-radius: 8px; text-align: center;">
                <div style="font-size: 16px; font-weight: bold; color: #dc3545; margin-bottom: 10px;">
                    🙏 MERCI POUR VOTRE CONFIANCE
                </div>
                <div style="color: #333; line-height: 1.6;">
                    Nous vous remercions pour le règlement de cette facture.<br>
                    Votre paiement nous permet de continuer à améliorer nos services.
                </div>
            </div>
        @else
            <div style="margin: 30px 0; padding: 20px; background-color: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; text-align: center;">
                <div style="font-size: 16px; font-weight: bold; color: #856404; margin-bottom: 10px;">
                    ⏳ FACTURE EN ATTENTE DE PAIEMENT
                </div>
                <div style="color: #856404; line-height: 1.6;">
                    Cette facture est en attente de règlement.<br>
                    Merci de procéder au paiement avant le {{ $protectedInvoice->due_date->format('d/m/Y') }}.
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>
                <strong>{{ $protectedInvoice->invoice_data['company']['name'] ?? 'CommercialiZe' }}</strong> - Facture générée avec CommercialiZe
            </div>
            <div class="generation-info">
                Document généré le {{ now()->format('d/m/Y à H:i') }}
            </div>
            <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; color: #495057; font-size: 11px;">
                🔒 <strong>Document officiel :</strong> Cette facture fait foi pour les services/produits fournis.
                <br>ID: {{ $protectedInvoice->id }} | Référence: {{ $protectedInvoice->formatted_invoice_number }}
            </div>
        </div>
    </div>
</body>
</html>