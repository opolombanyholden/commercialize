<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Facture Sécurisée</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn:hover { background: #0056b3; }
        .form-group { margin: 20px 0; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="text-center">
                <h1>🔐 Accès Sécurisé</h1>
                <h2>Facture {{ $protectedInvoice->invoice_number ?? 'N/A' }}</h2>
            </div>

            @if(session('success'))
                <div class="success">
                    <strong>✅ {{ session('success') }}</strong>
                </div>
            @endif

            @if(session('password_hint'))
                <div class="info">
                    <strong>🔑 Mot de passe (DEBUG) :</strong> <code>{{ session('password_hint') }}</code>
                </div>
            @endif

            @if($errors->any())
                <div style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;">
                    @foreach($errors->all() as $error)
                        <p>❌ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="info">
                <p><strong>📄 Facture :</strong> {{ $protectedInvoice->invoice_number ?? 'N/A' }}</p>
                @if(isset($protectedInvoice->invoice_data['client']['name']))
                    <p><strong>👤 Client :</strong> {{ $protectedInvoice->invoice_data['client']['name'] }}</p>
                @endif
                @if(isset($protectedInvoice->total_amount))
                    <p><strong>💰 Montant :</strong> {{ number_format($protectedInvoice->total_amount, 0, ',', ' ') }} FCFA</p>
                @endif
            </div>

            <form method="POST" action="{{ route('invoices.download-with-password', $protectedInvoice->id) }}">
                @csrf
                
                <div class="form-group">
                    <label for="password"><strong>🔑 Mot de passe de téléchargement :</strong></label>
                    <input type="password" 
                           id="password" 
                           name="password"
                           class="form-control"
                           placeholder="Saisissez le mot de passe reçu"
                           required 
                           autofocus>
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Le mot de passe vous a été envoyé après confirmation du paiement
                    </small>
                </div>

                <button type="submit" class="btn">
                    📥 Télécharger la facture PDF
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('invoices.create') }}" style="color: #666;">🔙 Retour à la création de facture</a>
            </div>

            <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; font-size: 14px; color: #666;">
                <strong>🔍 Debug Info :</strong><br>
                Facture ID: {{ $protectedInvoice->id ?? 'N/A' }}<br>
                Statut payé: {{ $protectedInvoice->is_paid ? 'Oui' : 'Non' }}<br>
                User ID: {{ $protectedInvoice->user_id ?? 'N/A' }}
            </div>
        </div>
    </div>
</body>
</html>