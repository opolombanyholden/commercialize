@extends('layouts.app')

@section('title', 'Accès au devis protégé - CommercialiZe')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <!-- Logo et titre -->
                        <div class="text-center mb-4">
                            <div class="logo-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-file-pdf" style="color: var(--primary-color);"></i>
                            </div>
                            <h2 class="fw-bold mb-2">
                                <span style="color: var(--primary-color);">Accès</span> 
                                <span style="color: var(--secondary-color);">Sécurisé</span>
                            </h2>
                            <p class="text-muted mb-0">Devis {{ $protectedPdf->quote_number }}</p>
                        </div>

                        <!-- Informations du devis -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Document protégé</strong><br>
                            Ce devis est sécurisé et nécessite un mot de passe pour être consulté.
                        </div>

                        <div class="mb-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">Montant</small>
                                    <div class="fw-bold text-success">
                                        {{ number_format($protectedPdf->total_amount, 0, ',', ' ') }} FCFA
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Statut</small>
                                    <div>
                                        @if($protectedPdf->is_paid)
                                            <span class="badge bg-success">Payé</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages d'erreur -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                @foreach ($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Formulaire de saisie du mot de passe -->
                        <form method="POST" action="{{ route('quotes.download-with-password', $protectedPdf->id) }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-key me-2 text-primary"></i>Mot de passe de déverrouillage
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password"
                                           placeholder="Saisissez le mot de passe reçu"
                                           required 
                                           autofocus>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Le mot de passe vous a été envoyé après confirmation du paiement
                                </small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-download me-2"></i>Télécharger le devis PDF
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <!-- Informations d'aide -->
                        <div class="text-center">
                            <h6 class="text-muted mb-3">Vous n'avez pas reçu le mot de passe ?</h6>
                            <div class="small text-muted">
                                <p class="mb-2">
                                    <i class="fas fa-clock me-1"></i>
                                    Le mot de passe est envoyé automatiquement après validation du paiement
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-envelope me-1"></i>
                                    Vérifiez votre boîte email et vos SMS
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-phone me-1"></i>
                                    Contactez-nous si vous ne l'avez pas reçu
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations de sécurité -->
                <div class="text-center mt-4">
                    <div class="card border-success">
                        <div class="card-body py-3">
                            <h6 class="text-success mb-2">
                                <i class="fas fa-shield-alt me-2"></i>Sécurité renforcée
                            </h6>
                            <p class="small text-muted mb-0">
                                Ce système de protection garantit que seuls les clients ayant effectué 
                                le paiement peuvent accéder au devis final.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endsection