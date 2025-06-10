@extends('layouts.app')

@section('title', 'Détails de la taxe - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-eye me-2 text-info"></i>Détails de la taxe
                    </h1>
                    <p class="text-muted mb-0">Informations complètes sur "{{ $tax->name }}"</p>
                </div>
                <div>
                    <a href="{{ route('taxes.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                    <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informations de la taxe
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Nom -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Nom de la taxe</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tag me-2 text-primary"></i>
                                <h4 class="mb-0">{{ $tax->name }}</h4>
                            </div>
                        </div>

                        <!-- Taux -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Taux de taxation</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-percentage me-2 text-primary"></i>
                                <h4 class="mb-0 text-success">{{ $tax->formatted_rate }}</h4>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold text-muted">Description</label>
                            <div class="d-flex align-items-start">
                                <i class="fas fa-align-left me-2 text-primary mt-1"></i>
                                <div>
                                    @if($tax->description)
                                        <p class="mb-0">{{ $tax->description }}</p>
                                    @else
                                        <em class="text-muted">Aucune description fournie</em>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Statut</label>
                            <div class="d-flex align-items-center">
                                @if($tax->is_active)
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="fas fa-check-circle me-2"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6 px-3 py-2">
                                        <i class="fas fa-pause-circle me-2"></i>Inactive
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $tax->is_active ? 'Cette taxe apparaît dans les documents' : 'Cette taxe n\'apparaît pas dans les documents' }}
                            </small>
                        </div>

                        <!-- Date de création -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Créée le</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar me-2 text-primary"></i>
                                <span>{{ $tax->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            <small class="text-muted">
                                Il y a {{ $tax->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions et calculateur -->
        <div class="col-lg-4 mb-4">
            <!-- Actions rapides -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Modifier -->
                        <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier cette taxe
                        </a>
                        
                        <!-- Toggle statut -->
                        <form method="POST" action="{{ route('taxes.toggle-status', $tax) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn btn-outline-{{ $tax->is_active ? 'secondary' : 'success' }} w-100"
                                    onclick="return confirm('Voulez-vous {{ $tax->is_active ? 'désactiver' : 'activer' }} cette taxe ?')">
                                <i class="fas fa-{{ $tax->is_active ? 'pause' : 'play' }} me-2"></i>
                                {{ $tax->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        
                        <!-- Supprimer -->
                        <form method="POST" action="{{ route('taxes.destroy', $tax) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement la taxe \'{{ $tax->name }}\' ?\n\nCette action est irréversible.')">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Calculateur de taxe -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator me-2"></i>Calculateur de taxe
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Montant HT (FCFA)</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="amount" 
                                   placeholder="1000"
                                   step="0.01"
                                   min="0">
                            <span class="input-group-text">FCFA</span>
                        </div>
                    </div>
                    
                    <div class="calculation-results" style="display: none;">
                        <div class="row text-center">
                            <div class="col-12 mb-2">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Montant de la taxe</small>
                                    <p class="h6 mb-0 text-primary" id="taxAmount">0 FCFA</p>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="p-2 bg-success text-white rounded">
                                    <small>Total TTC</small>
                                    <p class="h5 mb-0" id="totalAmount">0 FCFA</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Calcul basé sur le taux de <strong>{{ $tax->formatted_rate }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations supplémentaires -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Utilisation et statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                <h6>Date de création</h6>
                                <p class="mb-0">{{ $tax->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                                <h6>Dernière modification</h6>
                                <p class="mb-0">{{ $tax->updated_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-{{ $tax->is_active ? 'check-circle text-success' : 'pause-circle text-secondary' }} fa-2x mb-2"></i>
                                <h6>Statut</h6>
                                <p class="mb-0">{{ $tax->is_active ? 'Active' : 'Inactive' }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-file-invoice fa-2x text-info mb-2"></i>
                                <h6>Version</h6>
                                <p class="mb-0">Light</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Version Light :</strong> Les statistiques d'utilisation détaillées (nombre de documents utilisant cette taxe) 
                        seront disponibles dans les versions Standard, Pro et Enterprise.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Calculateur de taxe en temps réel
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    const taxRate = {{ $tax->rate }};
    
    if (amount > 0) {
        // Calculer la taxe et le total
        const taxAmount = Math.round((amount * taxRate / 100) * 100) / 100;
        const totalAmount = Math.round((amount + taxAmount) * 100) / 100;
        
        // Afficher les résultats
        document.getElementById('taxAmount').textContent = formatCurrency(taxAmount);
        document.getElementById('totalAmount').textContent = formatCurrency(totalAmount);
        document.querySelector('.calculation-results').style.display = 'block';
    } else {
        // Masquer les résultats si pas de montant
        document.querySelector('.calculation-results').style.display = 'none';
    }
});

// Fonction pour formater la devise
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount) + ' FCFA';
}

// Pré-remplir avec un exemple au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    amountInput.value = '10000';
    amountInput.dispatchEvent(new Event('input'));
});
</script>
@endpush
@endsection