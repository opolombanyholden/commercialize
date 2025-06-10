@extends('layouts.app')

@section('title', 'Créer une taxe - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>Créer une nouvelle taxe
                    </h1>
                    <p class="text-muted mb-0">Configurez un nouveau taux de taxe pour vos documents commerciaux</p>
                </div>
                <div>
                    <a href="{{ route('taxes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Formulaire de création -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percentage me-2"></i>Informations de la taxe
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Messages d'erreur -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Erreur(s) détectée(s) :</strong>
                            <ul class="mb-0 mt-2 list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('taxes.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Nom de la taxe -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    <i class="fas fa-tag me-2 text-primary"></i>Nom de la taxe *
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Ex: TPS Standard, TVA Export, etc."
                                       required 
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Le nom doit être unique et descriptif</small>
                            </div>

                            <!-- Taux de la taxe -->
                            <div class="col-md-6 mb-3">
                                <label for="rate" class="form-label fw-semibold">
                                    <i class="fas fa-percentage me-2 text-primary"></i>Taux (%) *
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('rate') is-invalid @enderror" 
                                           id="rate" 
                                           name="rate" 
                                           value="{{ old('rate') }}"
                                           placeholder="18.00"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           required>
                                    <span class="input-group-text">%</span>
                                    @error('rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Entre 0% et 100% (décimales autorisées)</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-2 text-primary"></i>Description (optionnelle)
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Description détaillée de cette taxe, son usage, les produits concernés...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 500 caractères</small>
                        </div>

                        <!-- Statut actif -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="fas fa-toggle-on me-2 text-success"></i>Taxe active
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Seules les taxes actives apparaîtront dans la liste de sélection des documents
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer la taxe
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Exemples de taxes communes au Gabon -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Taxes courantes au Gabon
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <h6 class="text-primary">TPS Standard</h6>
                                <p class="h4 text-success mb-2">18%</p>
                                <p class="small text-muted mb-0">
                                    Taux standard pour la plupart des biens et services
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <h6 class="text-primary">TPS Réduite</h6>
                                <p class="h4 text-warning mb-2">10%</p>
                                <p class="small text-muted mb-0">
                                    Taux réduit pour certains produits de première nécessité
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <h6 class="text-primary">Exonéré</h6>
                                <p class="h4 text-secondary mb-2">0%</p>
                                <p class="small text-muted mb-0">
                                    Pour les produits et services exemptés de taxes
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Rappel :</strong> Ces taux sont donnés à titre indicatif. 
                        Consultez toujours la réglementation fiscale en vigueur au Gabon.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Validation en temps réel du taux
document.getElementById('rate').addEventListener('input', function() {
    const rate = parseFloat(this.value);
    const feedback = this.parentNode.querySelector('.invalid-feedback');
    
    // Supprimer les messages d'erreur existants
    this.classList.remove('is-invalid');
    if (feedback) feedback.style.display = 'none';
    
    // Validation
    if (rate < 0) {
        this.classList.add('is-invalid');
        if (feedback) {
            feedback.textContent = 'Le taux ne peut pas être négatif';
            feedback.style.display = 'block';
        }
    } else if (rate > 100) {
        this.classList.add('is-invalid');
        if (feedback) {
            feedback.textContent = 'Le taux ne peut pas dépasser 100%';
            feedback.style.display = 'block';
        }
    }
});

// Prévisualisation du nom avec le taux
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('rate').addEventListener('input', updatePreview);

function updatePreview() {
    const name = document.getElementById('name').value;
    const rate = document.getElementById('rate').value;
    
    if (name && rate) {
        console.log(`Prévisualisation: ${name} (${rate}%)`);
    }
}

// Auto-complétion pour les noms de taxes courantes
const commonTaxes = [
    'TPS Standard',
    'TPS Réduite',
    'TPS Exportation',
    'Exonéré',
    'TVA Standard',
    'TVA Réduite'
];

// Vous pouvez implémenter une auto-complétion ici si nécessaire
</script>
@endpush
@endsection