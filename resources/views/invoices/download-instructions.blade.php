@extends('layouts.app')

@section('title', 'Instructions de téléchargement - Facture - CommercialiZe')

@section('content')
<div class="min-vh-100 py-5">
    <div class="container">
        <!-- En-tête principal -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-10 col-lg-8">
                <div class="text-center mb-4">
                    <div class="logo-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice-dollar" style="color: var(--primary-color);"></i>
                    </div>
                    <h1 class="fw-bold mb-2">
                        <span style="color: var(--primary-color);">Instructions de</span> 
                        <span style="color: var(--secondary-color);">Téléchargement</span>
                    </h1>
                    <p class="text-muted">Votre facture a été générée avec succès. Suivez les étapes ci-dessous pour la télécharger.</p>
                </div>
            </div>
        </div>

        <!-- Messages de succès -->
        @if(session('success'))
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 col-lg-8">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>{{ session('success') }}</strong>
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

        <!-- Informations de la facture générée -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <!-- En-tête de la facture -->
                        <div class="text-center mb-4">
                            <div class="logo-icon mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                            </div>
                            <h3 class="fw-bold mb-2">
                                <span style="color: var(--success-color);">Facture générée</span> 
                                <span style="color: var(--primary-color);">avec succès !</span>
                            </h3>
                            <p class="text-muted mb-0">{{ $protectedInvoice->formatted_invoice_number }}</p>
                        </div>

                        <!-- Informations de la facture -->
                        <div class="alert alert-success">
                            <i class="fas fa-file-invoice me-2"></i>
                            <strong>Document prêt :</strong> Votre facture a été générée et est prête au téléchargement après paiement.
                        </div>

                        <div class="mb-4">
                            <div class="row text-center">
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Client</small>
                                    <div class="fw-bold">{{ $protectedInvoice->invoice_data['client']['name'] }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Montant total</small>
                                    <div class="fw-bold text-success">{{ $protectedInvoice->formatted_total }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Date d'échéance</small>
                                    <div class="fw-bold">{{ $protectedInvoice->due_date->format('d/m/Y') }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Statut</small>
                                    <div><span class="badge {{ $protectedInvoice->status_class }}">{{ $protectedInvoice->status_label }}</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Alerte si facture en retard -->
                        @if($protectedInvoice->isOverdue())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention :</strong> Cette facture a dépassé sa date d'échéance de {{ abs($protectedInvoice->days_until_due) }} jour(s).
                            </div>
                        @endif

                        <hr class="my-4">

                        <!-- Étapes à suivre -->
                        <h5 class="fw-semibold mb-4">
                            <i class="fas fa-list-ol me-2 text-primary"></i>Étapes à suivre
                        </h5>
                        
                        <!-- Étape 1 : Télécharger les instructions -->
                        <div class="card border-primary mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                        1
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-2">Téléchargez le guide d'instructions (PDF)</h6>
                                        <p class="text-muted mb-3">Ce document contient tous les liens et informations nécessaires pour procéder au paiement et télécharger votre facture.</p>
                                        <div class="d-grid gap-2 d-md-flex">
                                            <a href="{{ route('invoices.download-instructions-pdf', $protectedInvoice->id) }}" class="btn btn-primary">
                                                <i class="fas fa-download me-2"></i>Télécharger les instructions PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 2 : Procéder au paiement -->
                        <div class="card border-warning mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                        2
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-2">Procédez au paiement</h6>
                                        <p class="text-muted mb-3">Utilisez le lien dans le PDF ou cliquez directement ci-dessous pour accéder à la page de paiement sécurisée.</p>
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-credit-card me-2"></i>
                                            <strong>Montant à payer :</strong> 500 FCFA (frais de téléchargement)
                                        </div>
                                        <div class="d-grid gap-2 d-md-flex">
                                            <a href="{{ route('invoices.payments', $protectedInvoice->id) }}" class="btn btn-warning">
                                                <i class="fas fa-credit-card me-2"></i>Procéder au paiement
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 3 : Recevoir le mot de passe -->
                        <div class="card border-info mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                        3
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-2">Recevez votre mot de passe</h6>
                                        <p class="text-muted mb-0">Après confirmation du paiement, vous recevrez automatiquement le mot de passe d'accès par email ou SMS selon votre choix.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 4 : Télécharger la facture -->
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                        4
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-2">Téléchargez votre facture PDF</h6>
                                        <p class="text-muted mb-3">Utilisez le lien d'accès sécurisé et le mot de passe reçu pour télécharger votre facture finale.</p>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-lock me-2"></i>
                                            <strong>Lien d'accès :</strong> {{ route('invoices.password-form', $protectedInvoice->id) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Informations importantes -->
                        <div class="alert alert-warning">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Informations importantes
                            </h6>
                            <ul class="mb-0" style="padding-left: 20px;">
                                <li>Conservez le PDF d'instructions jusqu'au téléchargement final</li>
                                <li>Le mot de passe sera envoyé uniquement après confirmation du paiement</li>
                                <li>Le lien d'accès à la facture est sécurisé et nécessite le mot de passe</li>
                                <li>Date limite de paiement : <strong>{{ $protectedInvoice->due_date->format('d/m/Y') }}</strong></li>
                                @if($protectedInvoice->isOverdue())
                                    <li class="text-danger"><strong>Cette facture est en retard de {{ abs($protectedInvoice->days_until_due) }} jour(s)</strong></li>
                                @endif
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-4">
                            <a href="{{ route('invoices.create') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-plus me-2"></i>Nouvelle facture
                            </a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('invoices.download-instructions-pdf', $protectedInvoice->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>PDF Instructions
                                </a>
                                <a href="{{ route('invoices.payments', $protectedInvoice->id) }}" class="btn btn-success">
                                    <i class="fas fa-credit-card me-2"></i>Payer maintenant
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support -->
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-question-circle me-2 text-info"></i>Besoin d'aide ?
                        </h6>
                        <p class="text-muted mb-3">
                            Si vous rencontrez des difficultés, n'hésitez pas à nous contacter :
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="mailto:{{ auth()->user()->email }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-envelope me-2"></i>{{ auth()->user()->email }}
                            </a>
                            @if(auth()->user()->phone)
                                <a href="tel:{{ auth()->user()->phone }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-phone me-2"></i>{{ auth()->user()->phone }}
                                </a>
                            @endif
                        </div>
                        <small class="text-muted mt-2 d-block">
                            Mentionnez toujours le numéro de facture : <strong>{{ $protectedInvoice->formatted_invoice_number }}</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.step-number {
    font-size: 1.1rem;
    min-width: 40px;
    min-height: 40px;
}
</style>
@endsection