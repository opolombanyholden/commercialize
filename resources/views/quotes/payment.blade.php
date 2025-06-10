@extends('layouts.app')

@section('title', 'Gestion des paiements - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-credit-card me-2 text-success"></i>Gestion des paiements
                    </h1>
                    <p class="text-muted mb-0">Vérifiez les paiements et débloquez les mots de passe PDF</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('password_sent'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('password_sent') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('quote_protected'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Devis {{ session('quote_protected.quote_number') }} généré :</strong><br>
            {{ session('quote_protected.message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0 list-unstyled">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Liste des devis en attente -->
        @if($pendingPdfs->count() > 0)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Devis en attente de paiement
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPdfs as $pdf)
                                        <tr>
                                            <td>
                                                <strong>{{ $pdf->quote_number }}</strong>
                                            </td>
                                            <td>
                                                @if($pdf->client_email)
                                                    <div>📧 {{ $pdf->client_email }}</div>
                                                @endif
                                                @if($pdf->client_phone)
                                                    <div>📱 {{ $pdf->client_phone }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    {{ number_format($pdf->total_amount, 0, ',', ' ') }} FCFA
                                                </span>
                                            </td>
                                            <td>
                                                {{ $pdf->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('quotes.password-form', $pdf->id) }}" 
                                                   class="btn btn-sm btn-outline-info me-2" 
                                                   target="_blank">
                                                    <i class="fas fa-external-link-alt"></i> Lien client
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulaire de déblocage -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-unlock me-2"></i>Déblocage de PDF après paiement
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quotes.unlock-pdf') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quote_number" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag me-2 text-primary"></i>Numéro de devis *
                                </label>
                                <input type="text" 
                                       class="form-control @error('quote_number') is-invalid @enderror" 
                                       id="quote_number" 
                                       name="quote_number" 
                                       value="{{ old('quote_number') }}"
                                       placeholder="DEV-2025-XXXX"
                                       required>
                                @error('quote_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_proof" class="form-label fw-semibold">
                                    <i class="fas fa-receipt me-2 text-primary"></i>Référence de paiement *
                                </label>
                                <input type="text" 
                                       class="form-control @error('payment_proof') is-invalid @enderror" 
                                       id="payment_proof" 
                                       name="payment_proof" 
                                       value="{{ old('payment_proof') }}"
                                       placeholder="Référence Mobile Money, virement, etc."
                                       required>
                                @error('payment_proof')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notification_method" class="form-label fw-semibold">
                                <i class="fas fa-paper-plane me-2 text-primary"></i>Méthode d'envoi du mot de passe *
                            </label>
                            <select class="form-select @error('notification_method') is-invalid @enderror" 
                                    id="notification_method" 
                                    name="notification_method" 
                                    required>
                                <option value="">Choisir la méthode...</option>
                                <option value="email" {{ old('notification_method') == 'email' ? 'selected' : '' }}>
                                    📧 Email (si renseigné dans le devis)
                                </option>
                                <option value="sms" {{ old('notification_method') == 'sms' ? 'selected' : '' }}>
                                    📱 SMS (si téléphone renseigné)
                                </option>
                            </select>
                            @error('notification_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-unlock-alt me-2"></i>Vérifier le paiement et envoyer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations sur le processus -->
        <div class="col-lg-4 mb-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Processus de déblocage
                    </h6>
                </div>
                <div class="card-body">
                    <div class="step mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary rounded-circle me-3">1</span>
                            <strong>Génération du devis</strong>
                        </div>
                        <small class="text-muted ms-4">
                            Le PDF est protégé par un mot de passe unique
                        </small>
                    </div>

                    <div class="step mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-warning rounded-circle me-3">2</span>
                            <strong>Attente du paiement</strong>
                        </div>
                        <small class="text-muted ms-4">
                            Le client effectue le paiement selon les modalités convenues
                        </small>
                    </div>

                    <div class="step mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success rounded-circle me-3">3</span>
                            <strong>Vérification</strong>
                        </div>
                        <small class="text-muted ms-4">
                            Vous vérifiez le paiement avec la référence fournie
                        </small>
                    </div>

                    <div class="step">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info rounded-circle me-3">4</span>
                            <strong>Déblocage</strong>
                        </div>
                        <small class="text-muted ms-4">
                            Le mot de passe est envoyé automatiquement au client
                        </small>
                    </div>
                </div>
            </div>

            <!-- Version Light - Limitations -->
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-star me-2"></i>Version Light
                    </h6>
                </div>
                <div class="card-body">
                    <small>
                        <strong>Fonctionnalités actuelles :</strong><br>
                        • Génération PDF protégé<br>
                        • Gestion manuelle des paiements<br>
                        • Notification par log système<br><br>
                        
                        <strong>Versions supérieures :</strong><br>
                        • Intégration Mobile Money<br>
                        • Envoi automatique email/SMS<br>
                        • Historique des paiements<br>
                        • Rapports financiers
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection