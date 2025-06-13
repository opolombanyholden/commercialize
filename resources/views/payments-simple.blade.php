<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Facture</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; }
        .form-group { margin: 15px 0; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧾 Paiement de Facture</h1>
        
        <div class="card success">
            <h3>✅ Facture générée avec succès !</h3>
            <p><strong>Numéro :</strong> {{ $protectedInvoice->invoice_number ?? 'N/A' }}</p>
            <p><strong>ID :</strong> {{ $protectedInvoice->id ?? 'N/A' }}</p>
            
            @if(isset($protectedInvoice->invoice_data['client']['name']))
                <p><strong>Client :</strong> {{ $protectedInvoice->invoice_data['client']['name'] }}</p>
            @endif
            
            @if(isset($protectedInvoice->total_amount))
                <p><strong>Montant :</strong> {{ number_format($protectedInvoice->total_amount, 0, ',', ' ') }} FCFA</p>
            @endif
        </div>

        <div class="card">
            <h3>💳 Simuler le Paiement</h3>
            <form action="{{ route('invoices.process-payment') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $protectedInvoice->id }}">
                
                <div class="form-group">
                    <label>Méthode de paiement :</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Virement bancaire</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Référence de paiement :</label>
                    <input type="text" name="payment_reference" class="form-control" value="TEST{{ rand(100000, 999999) }}" required>
                </div>
                
                <div class="form-group">
                    <label>Envoyer le mot de passe par :</label>
                    <select name="notification_method" class="form-control" required>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Contact client :</label>
                    <input type="text" name="client_contact" class="form-control" value="test@example.com" required>
                </div>
                
                <div class="form-group">
                    <strong>Montant à payer : {{ $downloadPrice }} FCFA</strong>
                </div>
                
                <button type="submit" class="btn">💳 Confirmer le paiement</button>
            </form>
        </div>
        
        <div class="card">
            <h4>🔍 Debug Info</h4>
            <p><strong>User ID :</strong> {{ auth()->id() }}</p>
            <p><strong>Facture User ID :</strong> {{ $protectedInvoice->user_id ?? 'N/A' }}</p>
            <p><strong>Plan :</strong> {{ $pricingPlan->name ?? 'N/A' }}</p>
        </div>
        
        <p><a href="{{ route('invoices.create') }}" class="btn">🔙 Retour</a></p>
    </div>
</body>
</html>