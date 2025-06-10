<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $quote['number'] }}</title>
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
            border-bottom: 3px solid #E91E63;
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
            color: #E91E63;
            margin-bottom: 10px;
        }
        
        .logo .brand-z {
            color: #8BC34A;
        }
        
        .company-info {
            color: #666;
            line-height: 1.6;
        }
        
        .quote-title {
            font-size: 24px;
            font-weight: bold;
            color: #E91E63;
            margin-bottom: 10px;
        }
        
        .quote-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .quote-info table {
            width: 100%;
        }
        
        .quote-info td {
            padding: 5px 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .quote-info td:first-child {
            font-weight: bold;
            width: 40%;
            color: #555;
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
        
        .client-title {
            font-size: 16px;
            font-weight: bold;
            color: #E91E63;
            margin-bottom: 15px;
            border-bottom: 2px solid #E91E63;
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
        
        /* Tableau des articles */
        .items-section {
            margin: 30px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #E91E63;
            margin-bottom: 15px;
            border-bottom: 2px solid #E91E63;
            padding-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: linear-gradient(135deg, #E91E63, #2196F3);
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
        
        .items-table tbody tr:hover {
            background-color: #e3f2fd;
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
        
        .totals-table .tax-row {
            font-size: 11px;
            color: #666;
        }
        
        .totals-table .final-total {
            background: linear-gradient(135deg, #E91E63, #2196F3);
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        
        /* Section taxes détail */
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
        
        /* Section notes */
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
        
        .notes-content {
            line-height: 1.6;
            color: #555;
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
        
        /* Utilitaires */
        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .currency {
            font-weight: bold;
            color: #2e7d32;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        /* Styles pour l'impression */
        @media print {
            .container {
                max-width: none;
                padding: 10px;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            .no-print {
                display: none;
            }
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
                    <strong>{{ $company['name'] }}</strong><br>
                    @if($company['address'])
                        {{ $company['address'] }}<br>
                    @endif
                    @if($company['phone'])
                        Tél: {{ $company['phone'] }}<br>
                    @endif
                    @if($company['email'])
                        Email: {{ $company['email'] }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="quote-title">DEVIS</div>
                <div class="quote-info">
                    <table>
                        <tr>
                            <td>Numéro :</td>
                            <td><strong>{{ $quote['number'] }}</strong></td>
                        </tr>
                        <tr>
                            <td>Date :</td>
                            <td>{{ $quote['date']->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Valide jusqu'au :</td>
                            <td>{{ $quote['valid_until']->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Type :</td>
                            <td>
                                <span class="highlight">
                                    {{ ucfirst($quote['type']) }}
                                    @if($quote['type'] === 'mixte')
                                        (Produits & Services)
                                    @endif
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Objet du devis -->
        <div style="margin-bottom: 25px; text-align: center;">
            <div style="font-size: 16px; font-weight: bold; color: #E91E63; margin-bottom: 8px;">
                OBJET DU DEVIS
            </div>
            <div style="font-size: 14px; padding: 10px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #E91E63;">
                {{ $quote['object'] }}
            </div>
        </div>

        <!-- Informations client -->
        <div class="client-section">
            <div class="client-info">
                <div class="client-title">INFORMATIONS CLIENT</div>
                <div class="client-details">
                    <div class="client-name">{{ $client['name'] }}</div>
                    @if($client['email'])
                        <div>Email: {{ $client['email'] }}</div>
                    @endif
                    @if($client['phone'])
                        <div>Téléphone: {{ $client['phone'] }}</div>
                    @endif
                    @if($client['address'])
                        <div>Adresse: {{ $client['address'] }}</div>
                    @endif
                    @if($client['city'])
                        <div>Ville: {{ $client['city'] }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Articles du devis -->
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
                    @foreach($items as $item)
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

        <!-- Détail des taxes appliquées -->
        @if(count($totals['taxes_details']) > 0)
            <div class="taxes-detail">
                <h4>📊 DÉTAIL DES TAXES APPLIQUÉES</h4>
                <div class="tax-breakdown">
                    @foreach($totals['taxes_details'] as $taxDetail)
                        <div style="margin-bottom: 5px;">
                            <strong>{{ $taxDetail['tax']->name }}</strong> ({{ $taxDetail['tax']->formatted_rate }}) 
                            appliquée sur 
                            @if($taxDetail['apply_on'] === 'total')
                                le total HT du devis
                            @elseif($taxDetail['apply_on'] === 'products')
                                les produits uniquement
                            @elseif($taxDetail['apply_on'] === 'services')
                                les services uniquement
                            @endif
                            : {{ number_format($taxDetail['base'], 0, ',', ' ') }} FCFA
                            → <strong>{{ number_format($taxDetail['amount'], 0, ',', ' ') }} FCFA</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section totaux -->
        <div class="totals-section">
            <div class="totals-left">
                <!-- Espace pour futures informations -->
            </div>
            <div class="totals-right">
                <table class="totals-table">
                    @if($totals['subtotal_products'] > 0)
                        <tr>
                            <td class="total-label">Total Produits HT :</td>
                            <td class="total-amount currency">{{ number_format($totals['subtotal_products'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    @if($totals['subtotal_services'] > 0)
                        <tr>
                            <td class="total-label">Total Services HT :</td>
                            <td class="total-amount currency">{{ number_format($totals['subtotal_services'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr class="subtotal-row">
                        <td class="total-label">Sous-total HT :</td>
                        <td class="total-amount currency">{{ number_format($totals['subtotal'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($totals['total_tax'] > 0)
                        <tr class="tax-row">
                            <td class="total-label">Total des taxes :</td>
                            <td class="total-amount currency">{{ number_format($totals['total_tax'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr class="final-total">
                        <td class="total-label">TOTAL TTC :</td>
                        <td class="total-amount">{{ number_format($totals['total'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes et conditions -->
        @if($quote['notes'])
            <div class="notes-section">
                <div class="notes-title">📋 NOTES ET CONDITIONS</div>
                <div class="notes-content">
                    {{ $quote['notes'] }}
                </div>
            </div>
        @endif

        <!-- Montants en lettres -->
        <div style="margin: 30px 0; padding: 20px; background-color: #f0f8ff; border: 2px solid #E91E63; border-radius: 8px;">
            <div style="text-align: center; margin-bottom: 15px;">
                <strong style="font-size: 14px; color: #E91E63;">💰 ARRÊTÉ DU PRÉSENT DEVIS</strong>
            </div>
            <div style="line-height: 1.8; color: #333;">
                <div style="margin-bottom: 10px;">
                    <strong>Sous-total HT :</strong> Arrêté le présent devis à la somme de 
                    <strong style="text-transform: uppercase;">{{ $totals['subtotal_words'] }}</strong> hors taxes.
                </div>
                <div>
                    <strong>Total TTC :</strong> Soit un montant total de 
                    <strong style="text-transform: uppercase;">{{ $totals['total_words'] }}</strong> toutes taxes comprises.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>
                <strong>{{ $company['name'] }}</strong> - Devis généré avec CommercialiZe Light
            </div>
            <div class="generation-info">
                Document généré le {{ $generated_at->format('d/m/Y à H:i') }}
            </div>
            @if(isset($pdf_protection) && $pdf_protection['is_protected'])
                <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; color: #856404; font-size: 11px;">
                    🔒 <strong>Document protégé :</strong> {{ $pdf_protection['message'] }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>