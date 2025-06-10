@extends('layouts.app')

@section('title', 'Gestion des taxes - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-percentage me-2 text-primary"></i>Gestion des taxes
                    </h1>
                    <p class="text-muted mb-0">Configurez et gérez vos taux de taxes (TPS) pour vos documents commerciaux</p>
                </div>
                <div>
                    <a href="{{ route('taxes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvelle taxe
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-blue));">
                <div class="card-body text-white">
                    <i class="fas fa-list fa-2x mb-3"></i>
                    <h4 class="card-title">{{ $stats['total'] }}</h4>
                    <p class="card-text">Taxes créées</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-teal));">
                <div class="card-body text-white">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <h4 class="card-title">{{ $stats['active'] }}</h4>
                    <p class="card-text">Taxes actives</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100" style="background: linear-gradient(135deg, var(--neutral-gray), var(--dark-gray));">
                <div class="card-body text-white">
                    <i class="fas fa-pause-circle fa-2x mb-3"></i>
                    <h4 class="card-title">{{ $stats['inactive'] }}</h4>
                    <p class="card-text">Taxes inactives</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des taxes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>Vos taxes configurées
                    </h5>
                    @if($taxes->count() > 0)
                        <small class="text-muted">{{ $taxes->count() }} taxe(s) au total</small>
                    @endif
                </div>
                <div class="card-body">
                    @if($taxes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Taux</th>
                                        <th>Description</th>
                                        <th>Statut</th>
                                        <th>Créée le</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($taxes as $tax)
                                        <tr class="{{ !$tax->is_active ? 'table-secondary' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-percentage me-2 text-primary"></i>
                                                    <strong>{{ $tax->name }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info fs-6 px-3 py-2">
                                                    {{ $tax->formatted_rate }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $tax->description ?: 'Aucune description' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($tax->is_active)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-pause-circle me-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $tax->created_at->format('d/m/Y à H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm d-flex justify-content-center" role="group">
                                                    <!-- Voir -->
                                                    <a href="{{ route('taxes.show', $tax) }}" 
                                                       class="btn btn-outline-info" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <!-- Modifier -->
                                                    <a href="{{ route('taxes.edit', $tax) }}" 
                                                       class="btn btn-outline-warning" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <!-- Toggle statut -->
                                                    <form method="POST" action="{{ route('taxes.toggle-status', $tax) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-outline-{{ $tax->is_active ? 'secondary' : 'success' }}" 
                                                                title="{{ $tax->is_active ? 'Désactiver' : 'Activer' }}"
                                                                onclick="return confirm('Voulez-vous {{ $tax->is_active ? 'désactiver' : 'activer' }} cette taxe ?')">
                                                            <i class="fas fa-{{ $tax->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Supprimer -->
                                                    <form method="POST" action="{{ route('taxes.destroy', $tax) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-outline-danger" 
                                                                title="Supprimer"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer la taxe \'{{ $tax->name }}\' ? Cette action est irréversible.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- État vide -->
                        <div class="text-center py-5">
                            <i class="fas fa-percentage fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted mb-3">Aucune taxe configurée</h5>
                            <p class="text-muted mb-4">
                                Commencez par créer vos premières taxes pour automatiser les calculs sur vos documents commerciaux.
                            </p>
                            <a href="{{ route('taxes.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Créer ma première taxe
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($taxes->count() > 0)
        <!-- Aide contextuelle -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-lightbulb me-2 text-warning"></i>Conseils pour la gestion des taxes
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>TPS Standard (18%)</strong> : Pour la plupart des produits et services
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>TPS Réduite (10%)</strong> : Pour certains produits de première nécessité
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Exonéré (0%)</strong> : Pour les produits/services exemptés de taxes
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        Seules les taxes <strong>actives</strong> apparaîtront dans vos documents
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection