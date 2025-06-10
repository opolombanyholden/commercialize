@extends('layouts.app')

@section('title', 'Tableau de bord - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header du dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Bonjour, {{ $user->name }} 👋</h1>
                    <p class="text-muted mb-0">
                        Bienvenue sur votre espace CommercialiZe {{ $stats['version'] }}
                        @if($user->company_name)
                            - {{ $user->company_name }}
                        @endif
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="fas fa-crown me-2"></i>{{ $stats['version'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-blue));">
                <div class="card-body text-white">
                    <i class="fas fa-percentage fa-2x mb-3"></i>
                    <h4 class="card-title">{{ $stats['taxes_count'] }}</h4>
                    <p class="card-text">Taxes configurées</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-teal));">
                <div class="card-body text-white">
                    <i class="fas fa-file-alt fa-2x mb-3"></i>
                    <h4 class="card-title">∞</h4>
                    <p class="card-text">Devis illimités</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--accent-orange), var(--accent-yellow));">
                <div class="card-body text-white">
                    <i class="fas fa-file-invoice fa-2x mb-3"></i>
                    <h4 class="card-title">∞</h4>
                    <p class="card-text">Factures illimitées</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--accent-red), var(--primary-color));">
                <div class="card-body text-white">
                    <i class="fas fa-truck fa-2x mb-3"></i>
                    <h4 class="card-title">∞</h4>
                    <p class="card-text">Bons de livraison</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('quotes.create') }}" class="btn btn-primary btn-lg w-100 py-3">
                                <i class="fas fa-file-alt fa-2x d-block mb-2"></i>
                                Créer un devis
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('invoices.create') }}" class="btn btn-success btn-lg w-100 py-3">
                                <i class="fas fa-file-invoice fa-2x d-block mb-2"></i>
                                Créer une facture
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('deliveries.create') }}" class="btn btn-warning btn-lg w-100 py-3">
                                <i class="fas fa-truck fa-2x d-block mb-2"></i>
                                Bon de livraison
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration et informations -->
    <div class="row">
        <!-- Gestion des taxes -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percentage me-2 text-primary"></i>Gestion des taxes
                    </h5>
                    <a href="{{ route('taxes.index') }}" class="btn btn-sm btn-outline-primary">
                        Gérer <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Configurez et gérez vos taux de taxes (TPS) pour le calcul automatique 
                        sur vos documents commerciaux.
                    </p>
                    
                    @if($user->taxes->count() > 0)
                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">Taxes configurées :</small>
                            @foreach($user->activeTaxes->take(3) as $tax)
                                <span class="badge bg-light text-dark me-2 mb-1">
                                    {{ $tax->name }} ({{ $tax->formatted_rate }})
                                </span>
                            @endforeach
                            @if($user->activeTaxes->count() > 3)
                                <span class="badge bg-secondary">+{{ $user->activeTaxes->count() - 3 }} autres</span>
                            @endif
                        </div>
                    @endif
                    
                    <div class="d-grid">
                        <a href="{{ route('taxes.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>Configurer les taxes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations compte -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>Informations du compte
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Version :</small>
                            <p class="fw-semibold">{{ $stats['version'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Membre depuis :</small>
                            <p class="fw-semibold">{{ $stats['user_since'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Email :</small>
                            <p class="fw-semibold">{{ $user->email }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Pays :</small>
                            <p class="fw-semibold">{{ $user->country }}</p>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-edit me-2"></i>Modifier le profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fonctionnalités version Light -->
    <div class="row">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2"></i>CommercialiZe Light - Fonctionnalités incluses
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Devis PDF</strong> - Génération instantanée
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Factures PDF</strong> - Avec calcul automatique des taxes
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Bons de livraison</strong> - Gestion simple et efficace
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Taxes TPS Gabon</strong> - Calcul automatique conforme
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Interface moderne</strong> - Simple et intuitive
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Utilisation illimitée</strong> - Sans restriction
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Version Light :</strong> Parfaite pour les auto-entrepreneurs et TPE. 
                        Les documents ne sont pas stockés - génération à la demande pour un usage immédiat.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection