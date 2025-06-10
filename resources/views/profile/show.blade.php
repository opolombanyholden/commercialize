@extends('layouts.app')

@section('title', 'Mon Profil - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-circle me-2 text-primary"></i>Mon Profil
                    </h1>
                    <p class="text-muted mb-0">Gérez vos informations personnelles et d'entreprise</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="name" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" value="{{ auth()->user()->email }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" value="{{ auth()->user()->phone ?? 'Non renseigné' }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="country" value="{{ auth()->user()->country }}" readonly>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Version Light :</strong> La modification du profil sera disponible dans les versions supérieures.
                            <a href="#" class="alert-link">Découvrir les versions Standard, Pro et Enterprise</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations entreprise -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Entreprise
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nom de l'entreprise</label>
                        <p class="form-control-plaintext">
                            {{ auth()->user()->company_name ?? 'Non renseigné' }}
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adresse</label>
                        <p class="form-control-plaintext">
                            {{ auth()->user()->full_address ?? 'Non renseignée' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Version</label>
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="fas fa-star me-1"></i>{{ auth()->user()->version_name }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Membre depuis</label>
                        <p class="form-control-plaintext">
                            {{ auth()->user()->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques du compte -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statistiques de votre compte
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                                <h4>{{ auth()->user()->taxes->count() }}</h4>
                                <p class="mb-0">Taxes configurées</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-calendar fa-2x text-success mb-2"></i>
                                <h4>{{ auth()->user()->created_at->diffInDays(now()) }}</h4>
                                <p class="mb-0">Jours d'utilisation</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                                <h4>Light</h4>
                                <p class="mb-0">Version active</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                                <h4>{{ auth()->user()->is_active ? 'Actif' : 'Inactif' }}</h4>
                                <p class="mb-0">Statut du compte</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title">Actions rapides depuis votre profil</h5>
                    <div class="btn-group flex-wrap" role="group">
                        <a href="{{ route('taxes.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-percentage me-2"></i>Gérer les taxes
                        </a>
                        <a href="{{ route('quotes.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-file-alt me-2"></i>Nouveau devis
                        </a>
                        <a href="{{ route('invoices.create') }}" class="btn btn-outline-warning">
                            <i class="fas fa-file-invoice me-2"></i>Nouvelle facture
                        </a>
                        <a href="{{ route('deliveries.create') }}" class="btn btn-outline-info">
                            <i class="fas fa-truck me-2"></i>Bon de livraison
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection