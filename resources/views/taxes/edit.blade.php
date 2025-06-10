@extends('layouts.app')

@section('title', 'Modifier une taxe - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-edit me-2 text-warning"></i>Modifier la taxe "{{ $tax->name }}"
                    </h1>
                    <p class="text-muted mb-0">Modifiez les paramètres de cette taxe</p>
                </div>
                <div>
                    <a href="{{ route('taxes.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                    <a href="{{ route('taxes.show', $tax) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-2"></i>Voir les détails
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Informations actuelles -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Valeurs actuelles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Nom actuel :</small>
                            <p class="fw-bold mb-0">{{ $tax->name }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Taux actuel :</small>
                            <p class="fw-bold mb-0 text-primary">{{ $tax->formatted_rate }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Statut :</small>
                            <p class="mb-0">
                                @if($tax->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Créée le :</small>
                            <p class="fw-bold mb-0">{{ $tax->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percentage me-2"></i>Nouvelles informations
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

                    <form method="POST" action="{{ route('taxes.update', $tax) }}">
                        @csrf
                        @method('PUT')
                        
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
                                       value="{{ old('name', $tax->name) }}"
                                       placeholder="Ex: TPS Standard, TVA Export, etc."
                                       required 
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Le nom doit être unique parmi vos taxes</small>
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
                                           value="{{ old('rate', $tax->rate) }}"
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
                                      placeholder="Description détaillée de cette taxe, son usage, les produits concernés...">{{ old('description', $tax->description) }}</textarea>
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
                                       {{ old('is_active', $tax->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="fas fa-toggle-on me-2 text-success"></i>Taxe active
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Seules les taxes actives apparaîtront dans la liste de sélection des documents
                                </div>
                            </div>
                        </div>

                        <!-- Indicateur de modifications -->
                        <div class="alert alert-warning" id="changesAlert" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Modifications détectées !</strong> N'oubliez pas de sauvegarder vos changements.
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('taxes.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="button" class="btn btn-outline-info" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </button>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Mettre à jour la taxe
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Actions supplémentaires -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <!-- Toggle statut -->
                            <form method="POST" action="{{ route('taxes.toggle-status', $tax) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn btn-outline-{{ $tax->is_active ? 'secondary' : 'success' }} w-100"
                                        onclick="return confirm('Voulez-vous {{ $tax->is_active ? 'désactiver' : 'activer' }} cette taxe ?')">
                                    <i class="fas fa-{{ $tax->is_active ? 'pause' : 'play' }} me-2"></i>
                                    {{ $tax->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        </div>
                        
                        <div class="col-md-4 mb-2">
                            <!-- Voir détails -->
                            <a href="{{ route('taxes.show', $tax) }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-eye me-2"></i>Voir les détails
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-2">
                            <!-- Supprimer -->
                            <form method="POST" action="{{ route('taxes.destroy', $tax) }}" class="d-inline w-100">
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
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Valeurs originales pour détecter les changements
const originalValues = {
    name: '{{ $tax->name }}',
    rate: '{{ $tax->rate }}',
    description: '{{ $tax->description ?? '' }}',
    is_active: {{ $tax->is_active ? 'true' : 'false' }}
};

// Fonction pour vérifier si le formulaire a été modifié
function checkForChanges() {
    const currentValues = {
        name: document.getElementById('name').value,
        rate: document.getElementById('rate').value,
        description: document.getElementById('description').value,
        is_active: document.getElementById('is_active').checked
    };
    
    const hasChanges = 
        currentValues.name !== originalValues.name ||
        parseFloat(currentValues.rate) !== parseFloat(originalValues.rate) ||
        currentValues.description !== originalValues.description ||
        currentValues.is_active !== originalValues.is_active;
    
    const alert = document.getElementById('changesAlert');
    if (hasChanges) {
        alert.style.display = 'block';
    } else {
        alert.style.display = 'none';
    }
    
    return hasChanges;
}

// Fonction pour réinitialiser le formulaire
function resetForm() {
    if (confirm('Voulez-vous vraiment annuler toutes vos modifications ?')) {
        document.getElementById('name').value = originalValues.name;
        document.getElementById('rate').value = originalValues.rate;
        document.getElementById('description').value = originalValues.description;
        document.getElementById('is_active').checked = originalValues.is_active;
        
        // Masquer l'alerte
        document.getElementById('changesAlert').style.display = 'none';
    }
}

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
    
    // Vérifier les changements
    checkForChanges();
});

// Écouter les changements sur tous les champs
document.getElementById('name').addEventListener('input', checkForChanges);
document.getElementById('description').addEventListener('input', checkForChanges);
document.getElementById('is_active').addEventListener('change', checkForChanges);

// Confirmation avant de quitter si modifications non sauvegardées
window.addEventListener('beforeunload', function(e) {
    if (checkForChanges()) {
        e.preventDefault();
        e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
    }
});
</script>
@endpush
@endsection