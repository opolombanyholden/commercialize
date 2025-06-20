@extends('layouts.app')

@section('title', 'Gestion des Paiements - CommercialiZe')

@section('content')
<div class="min-vh-100 py-5">
    <div class="container">
        <!-- En-tête principal -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-10 col-lg-8">
                <div class="text-center mb-4">
                    <div class="logo-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="fas fa-credit-card" style="color: var(--primary-color);"></i>
                    </div>
                    <h1 class="fw-bold mb-2">
                        <span style="color: var(--primary-color);">Gestion des</span> 
                        <span style="color: var(--secondary-color);">Paiements</span>
                    </h1>
                    <p class="text-muted">Procédez au paiement pour télécharger vos devis PDF sécurisés</p>
                </div>
            </div>
        </div>

        <!-- Messages de succès avec bouton de téléchargement -->
        @if(session('success'))
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 col-lg-8">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>{{ session('success') }}</strong>
                        
                        @if(session('download_instructions'))
                            <div class="mt-3">
                                <a href="{{ route('quotes.download-instructions') }}" 
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-download me-2"></i>Télécharger le PDF d'instructions
                                </a>
                            </div>
                        @endif
                        
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Message de mot de passe envoyé -->
        @if(session('password_sent'))
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 col-lg-8">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-envelope me-2"></i>
                        <strong>{{ session('password_sent') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Messages d'erreur -->
        @if($errors->any())
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 col-lg-8">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erreur(s) détectée(s) :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Informations de facturation -->
        @if(isset($pricingPlan))
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 col-lg-8">
                    <div class="card border-info">
                        <div class="card-body">
                            <h5 class="card-title text-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>Informations de facturation
                            </h5>
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="text-muted">Plan actuel</small>
                                    <div class="fw-bold text-primary">{{ $pricingPlan->name ?? 'Light' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Prix par téléchargement</small>
                                    <div class="fw-bold text-success">{{ number_format($downloadPrice ?? 500, 0, ',', ' ') }} FCFA</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Solde actuel</small>
                                    <div class="fw-bold text-warning">{{ auth()->user()->formatted_balance ?? '0 FCFA' }}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <a href="{{ route('pricing.plans') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-exchange-alt me-1"></i>Changer de plan
                                </a>
                                <a href="{{ route('pricing.billing') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-plus me-1"></i>Recharger
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Devis récemment généré -->
        @if(isset($lastGeneratedQuote) && $lastGeneratedQuote)
            <div class="row justify-content-center mb-5">
                <div class="col-md-10 col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <!-- En-tête du devis -->
                            <div class="text-center mb-4">
                                <div class="logo-icon mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="fas fa-file-invoice" style="color: var(--success-color);"></i>
                                </div>
                                <h3 class="fw-bold mb-2">
                                    <span style="color: var(--success-color);">Devis généré</span> 
                                    <span style="color: var(--primary-color);">avec succès</span>
                                </h3>
                                <p class="text-muted mb-0">{{ $lastGeneratedQuote->quote_number }}</p>
                            </div>

                            <!-- Informations du devis -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Document prêt</strong><br>
                                Votre devis a été généré et est prêt au téléchargement après paiement.
                            </div>

                            <div class="mb-4">
                                <div class="row text-center">
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">Client</small>
                                        <div class="fw-bold">{{ $lastGeneratedQuote->quote_data['client']['name'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">Montant total</small>
                                        <div class="fw-bold text-success">{{ number_format($lastGeneratedQuote->total_amount, 0, ',', ' ') }} FCFA</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">Date</small>
                                        <div class="fw-bold">{{ $lastGeneratedQuote->created_at->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">Statut</small>
                                        <div><span class="badge bg-warning">En attente</span></div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Formulaire de paiement -->
                            <h5 class="fw-semibold mb-4">
                                <i class="fas fa-credit-card me-2 text-primary"></i>Procéder au paiement
                            </h5>
                            
                            <form action="{{ route('quotes.process-payment') }}" method="POST">
                                @csrf
                                <input type="hidden" name="quote_id" value="{{ $lastGeneratedQuote->id }}">

                                <div class="row g-3">
                                    <!-- Méthode de paiement -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-wallet me-1"></i>Méthode de paiement *
                                        </label>
                                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                            <option value="">Sélectionnez une méthode</option>
                                            <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>
                                                <i class="fas fa-mobile-alt"></i> Mobile Money (Orange/Moov)
                                            </option>
                                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                                                <i class="fas fa-university"></i> Virement bancaire
                                            </option>
                                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                                <i class="fas fa-money-bill"></i> Espèces
                                            </option>
                                        </select>
                                        @error('payment_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Méthode de notification -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-bell me-1"></i>Envoyer le mot de passe par *
                                        </label>
                                        <select name="notification_method" class="form-select @error('notification_method') is-invalid @enderror" required>
                                            <option value="">Sélectionnez une méthode</option>
                                            <option value="email" {{ old('notification_method') == 'email' ? 'selected' : '' }}>
                                                <i class="fas fa-envelope"></i> Email
                                            </option>
                                            <option value="sms" {{ old('notification_method') == 'sms' ? 'selected' : '' }}>
                                                <i class="fas fa-sms"></i> SMS
                                            </option>
                                        </select>
                                        @error('notification_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Référence de paiement -->
                                    <div class="col-md-6">
                                        <label for="payment_reference" class="form-label fw-semibold">
                                            <i class="fas fa-receipt me-1"></i>Référence de paiement *
                                        </label>
                                        <input type="text" 
                                               id="payment_reference" 
                                               name="payment_reference" 
                                               value="{{ old('payment_reference') }}"
                                               class="form-control @error('payment_reference') is-invalid @enderror"
                                               placeholder="Ex: TXN123456, REF789012..."
                                               required>
                                        <small class="text-muted">Référence de votre transaction (min. 6 caractères)</small>
                                        @error('payment_reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Contact client -->
                                    <div class="col-md-6">
                                        <label for="client_contact" class="form-label fw-semibold">
                                            <i class="fas fa-address-book me-1"></i>Contact client *
                                        </label>
                                        <input type="text" 
                                               id="client_contact" 
                                               name="client_contact" 
                                               value="{{ old('client_contact', $lastGeneratedQuote->client_email ?? $lastGeneratedQuote->client_phone) }}"
                                               class="form-control @error('client_contact') is-invalid @enderror"
                                               placeholder="email@exemple.com ou +241 XX XX XX XX"
                                               required>
                                        <small class="text-muted">Email ou téléphone pour recevoir le mot de passe</small>
                                        @error('client_contact')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Résumé du paiement -->
                                <div class="alert alert-warning mt-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">
                                            <i class="fas fa-calculator me-2"></i>Montant à payer :
                                        </span>
                                        <span class="fs-4 fw-bold text-primary">{{ number_format($downloadPrice ?? 500, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    <a href="{{ route('quotes.create') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-plus me-2"></i>Nouveau devis
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg px-4">
                                        <i class="fas fa-credit-card me-2"></i>Confirmer le paiement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Liste des devis en attente -->
        @if(isset($pendingPdfs) && $pendingPdfs->count() > 0)
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <div class="card shadow border-0">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-hourglass-half me-2 text-warning"></i>
                                Devis en attente de paiement ({{ $pendingPdfs->count() }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Devis</th>
                                            <th>Client</th>
                                            <th>Montant</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingPdfs as $pdf)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="fw-semibold">{{ $pdf->quote_number }}</div>
                                                        <small class="text-muted">{{ $pdf->filename }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>{{ $pdf->quote_data['client']['name'] ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $pdf->client_email ?? $pdf->client_phone ?? 'Contact non défini' }}</small>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ number_format($pdf->total_amount, 0, ',', ' ') }} FCFA</div>
                                                    <small class="text-muted">Téléchargement : {{ number_format($downloadPrice ?? 500, 0, ',', ' ') }} FCFA</small>
                                                </td>
                                                <td>
                                                    <div>{{ $pdf->created_at->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ $pdf->created_at->format('H:i') }}</small>
                                                </td>
                                                <td>
                                                    <button onclick="openPaymentModal({{ $pdf->id }}, '{{ $pdf->quote_number }}')" 
                                                            class="btn btn-primary btn-sm">
                                                        <i class="fas fa-credit-card me-1"></i>Payer
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- État vide -->
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-success">
                        <div class="card-body text-center py-5">
                            <div class="logo-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Aucun devis en attente</h4>
                            <p class="text-muted mb-4">Vous n'avez aucun devis en attente de paiement.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer un nouveau devis
                                </a>
                                <a href="{{ route('pricing.billing') }}" class="btn btn-outline-success">
                                    <i class="fas fa-wallet me-2"></i>Recharger mon compte
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal de paiement pour les anciens devis -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card me-2"></i>Paiement devis <span id="modalQuoteNumber"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('quotes.process-payment') }}" method="POST" id="modalPaymentForm">
                    @csrf
                    <input type="hidden" name="quote_id" id="modalQuoteId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Méthode de paiement *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Sélectionnez...</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="cash">Espèces</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Référence de paiement *</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="Ex: TXN123456..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Envoyer le mot de passe par *</label>
                        <select name="notification_method" class="form-select" required>
                            <option value="">Sélectionnez...</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact client *</label>
                        <input type="text" name="client_contact" class="form-control" placeholder="email@exemple.com ou téléphone" required>
                    </div>

                    <div class="alert alert-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Montant à payer :</span>
                            <span class="fw-bold">{{ number_format($downloadPrice ?? 500, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="modalPaymentForm" class="btn btn-primary">
                    <i class="fas fa-credit-card me-2"></i>Confirmer le paiement
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openPaymentModal(quoteId, quoteNumber) {
    document.getElementById('modalQuoteId').value = quoteId;
    document.getElementById('modalQuoteNumber').textContent = quoteNumber;
    
    // Utiliser Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}
</script>
@endsection