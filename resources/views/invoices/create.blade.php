@extends('layouts.app')

@section('title', 'Créer une facture - CommercialiZe')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-file-invoice me-2 text-primary"></i>Créer une facture
                    </h1>
                    <p class="text-muted mb-0">Générez une facture PDF professionnelle avec calcul automatique des taxes</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages d'erreur globaux -->
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

    <form method="POST" action="{{ route('invoices.generate') }}" id="invoiceForm">
        @csrf
        
        <div class="row">
            <!-- Informations de la facture -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informations de la facture
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="invoice_number" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag me-2 text-primary"></i>Numéro de facture *
                                </label>
                                <input type="text" 
                                       class="form-control @error('invoice_number') is-invalid @enderror" 
                                       id="invoice_number" 
                                       name="invoice_number" 
                                       value="{{ old('invoice_number', 'FAC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)) }}"
                                       required>
                                @error('invoice_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="invoice_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>Date de facture *
                                </label>
                                <input type="date" 
                                       class="form-control @error('invoice_date') is-invalid @enderror" 
                                       id="invoice_date" 
                                       name="invoice_date" 
                                       value="{{ old('invoice_date', date('Y-m-d')) }}"
                                       required>
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="invoice_object" class="form-label fw-semibold">
                                    <i class="fas fa-tag me-2 text-primary"></i>Objet de la facture *
                                </label>
                                <input type="text" 
                                       class="form-control @error('invoice_object') is-invalid @enderror" 
                                       id="invoice_object" 
                                       name="invoice_object" 
                                       value="{{ old('invoice_object') }}"
                                       placeholder="Ex: Vente d'équipements informatiques et services"
                                       required>
                                @error('invoice_object')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="invoice_type" class="form-label fw-semibold">
                                    <i class="fas fa-layer-group me-2 text-primary"></i>Type de facture *
                                </label>
                                <select class="form-select @error('invoice_type') is-invalid @enderror" 
                                        id="invoice_type" 
                                        name="invoice_type" 
                                        required>
                                    <option value="">Sélectionner le type</option>
                                    <option value="produit" {{ old('invoice_type') == 'produit' ? 'selected' : '' }}>Produits uniquement</option>
                                    <option value="service" {{ old('invoice_type') == 'service' ? 'selected' : '' }}>Services uniquement</option>
                                    <option value="mixte" {{ old('invoice_type') == 'mixte' ? 'selected' : '' }}>Produits et Services (Mixte)</option>
                                </select>
                                @error('invoice_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">La TPS ne s'applique que sur les services</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label fw-semibold">
                                    <i class="fas fa-clock me-2 text-primary"></i>Date d'échéance *
                                </label>
                                <input type="date" 
                                       class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" 
                                       name="due_date" 
                                       value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}"
                                       required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Date limite de paiement</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations client -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>Informations client
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="client_name" class="form-label fw-semibold">
                                <i class="fas fa-user-circle me-2 text-primary"></i>Nom du client *
                            </label>
                            <input type="text" 
                                   class="form-control @error('client_name') is-invalid @enderror" 
                                   id="client_name" 
                                   name="client_name" 
                                   value="{{ old('client_name') }}"
                                   placeholder="Nom complet ou raison sociale"
                                   required>
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Email
                                </label>
                                <input type="email" 
                                       class="form-control @error('client_email') is-invalid @enderror" 
                                       id="client_email" 
                                       name="client_email" 
                                       value="{{ old('client_email') }}"
                                       placeholder="client@email.com">
                                @error('client_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="client_phone" class="form-label fw-semibold">
                                    <i class="fas fa-phone me-2 text-primary"></i>Téléphone
                                </label>
                                <input type="tel" 
                                       class="form-control @error('client_phone') is-invalid @enderror" 
                                       id="client_phone" 
                                       name="client_phone" 
                                       value="{{ old('client_phone') }}"
                                       placeholder="+241 XX XX XX XX">
                                @error('client_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="client_address" class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Adresse
                                </label>
                                <input type="text" 
                                       class="form-control @error('client_address') is-invalid @enderror" 
                                       id="client_address" 
                                       name="client_address" 
                                       value="{{ old('client_address') }}"
                                       placeholder="Adresse complète">
                                @error('client_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="client_city" class="form-label fw-semibold">
                                    <i class="fas fa-city me-2 text-primary"></i>Ville
                                </label>
                                <input type="text" 
                                       class="form-control @error('client_city') is-invalid @enderror" 
                                       id="client_city" 
                                       name="client_city" 
                                       value="{{ old('client_city', 'Libreville') }}"
                                       placeholder="Libreville">
                                @error('client_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles de la facture -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Articles de la facture
                        </h5>
                        <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                            <i class="fas fa-plus me-2"></i>Ajouter un article
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            <!-- Les articles seront ajoutés ici par JavaScript -->
                        </div>
                        
                        <div class="alert alert-info mt-3" id="noItemsAlert">
                            <i class="fas fa-info-circle me-2"></i>
                            Cliquez sur "Ajouter un article" pour commencer à saisir les éléments de votre facture.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Taxes appliquées -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-percentage me-2"></i>Taxes appliquées
                        </h5>
                        <button type="button" class="btn btn-warning btn-sm" id="addTaxBtn">
                            <i class="fas fa-plus me-2"></i>Ajouter une taxe
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="taxesContainer">
                            <!-- Les taxes seront ajoutées ici par JavaScript -->
                        </div>
                        
                        <div class="alert alert-warning mt-3" id="noTaxesAlert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important :</strong> Les taxes s'appliquent sur le total HT de la facture. 
                            La TPS ne s'applique que sur la partie "services" de la facture.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totaux et notes -->
        <div class="row mt-4">
            <div class="col-lg-8">
                <!-- Notes -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-sticky-note me-2"></i>Notes et conditions de paiement (optionnel)
                        </h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4"
                                  placeholder="Conditions de paiement, modalités, informations bancaires...">{{ old('notes', 'Paiement à 30 jours. Pénalités de retard applicables selon la réglementation en vigueur.') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Récapitulatif des totaux -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calculator me-2"></i>Récapitulatif
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Produits HT :</span>
                            <strong id="subtotalProducts">0 FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Services HT :</span>
                            <strong id="subtotalServices">0 FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Sous-total HT :</strong></span>
                            <strong id="subtotalAmount">0 FCFA</strong>
                        </div>
                        <hr>
                        <div id="taxesBreakdown">
                            <!-- Détail des taxes sera affiché ici -->
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Total taxes :</strong></span>
                            <strong id="totalTaxAmount">0 FCFA</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="h6">Total TTC :</span>
                            <strong class="h5 text-success" id="totalAmount">0 FCFA</strong>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="generateBtn" disabled>
                                <i class="fas fa-file-pdf me-2"></i>Générer la facture PDF
                            </button>
                        </div>
                        
                        <small class="text-muted mt-2 d-block text-center">
                            Le PDF sera généré et téléchargé automatiquement
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Données des taxes disponibles depuis PHP
const taxes = @json($taxes);

// Compteurs pour générer des IDs uniques
let itemCounter = 0;
let taxCounter = 0;

// Template HTML pour un article
function itemTemplate(index) {
    return `
        <div class="item-row border rounded p-3 mb-3" data-index="${index}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Description *</label>
                    <input type="text" 
                           class="form-control item-description" 
                           name="items[${index}][description]" 
                           placeholder="Description du produit/service"
                           required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Type *</label>
                    <select class="form-select item-type" name="items[${index}][type]" required>
                        <option value="">Type...</option>
                        <option value="produit">Produit</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3">
                    <label class="form-label fw-semibold">Qté *</label>
                    <input type="number" 
                           class="form-control item-quantity" 
                           name="items[${index}][quantity]" 
                           placeholder="1"
                           step="0.01"
                           min="0.01"
                           required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Prix unitaire *</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control item-unit-price" 
                               name="items[${index}][unit_price]" 
                               placeholder="0"
                               step="0.01"
                               min="0"
                               required>
                        <span class="input-group-text">FCFA</span>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Total HT</label>
                    <div class="text-center">
                        <strong class="item-total">0 FCFA</strong>
                    </div>
                </div>
                <div class="col-md-1 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Template HTML pour une taxe
function taxTemplate(index) {
    const taxOptions = taxes.map(tax => 
        `<option value="${tax.id}" data-rate="${tax.rate}">${tax.display_name}</option>`
    ).join('');
    
    return `
        <div class="tax-row border rounded p-3 mb-3" data-index="${index}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Taxe *</label>
                    <select class="form-select tax-select" name="applied_taxes[${index}][tax_id]" required>
                        <option value="">Choisir une taxe...</option>
                        ${taxOptions}
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Appliquer sur *</label>
                    <select class="form-select tax-apply-on" name="applied_taxes[${index}][apply_on]" required>
                        <option value="">Choisir...</option>
                        <option value="total">Total HT de la facture</option>
                        <option value="products">Produits uniquement</option>
                        <option value="services">Services uniquement</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Base de calcul</label>
                    <div class="text-center">
                        <strong class="tax-base">0 FCFA</strong>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Montant taxe</label>
                    <div class="text-center">
                        <strong class="tax-amount">0 FCFA</strong>
                    </div>
                </div>
                <div class="col-md-1 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-tax" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Ajouter un article
document.getElementById('addItemBtn').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    container.insertAdjacentHTML('beforeend', itemTemplate(itemCounter));
    
    document.getElementById('noItemsAlert').style.display = 'none';
    
    attachItemEventListeners(itemCounter);
    itemCounter++;
    updateTotals();
});

// Ajouter une taxe
document.getElementById('addTaxBtn').addEventListener('click', function() {
    const container = document.getElementById('taxesContainer');
    container.insertAdjacentHTML('beforeend', taxTemplate(taxCounter));
    
    document.getElementById('noTaxesAlert').style.display = 'none';
    
    attachTaxEventListeners(taxCounter);
    taxCounter++;
    updateTotals();
});

// Attacher les événements pour un article
function attachItemEventListeners(index) {
    const row = document.querySelector(`.item-row[data-index="${index}"]`);
    if (!row) return;
    
    row.querySelector('.item-quantity').addEventListener('input', updateTotals);
    row.querySelector('.item-unit-price').addEventListener('input', updateTotals);
    row.querySelector('.item-type').addEventListener('change', updateTotals);
    
    row.querySelector('.remove-item').addEventListener('click', function() {
        if (confirm('Voulez-vous supprimer cet article ?')) {
            row.remove();
            updateTotals();
            
            if (document.querySelectorAll('.item-row').length === 0) {
                document.getElementById('noItemsAlert').style.display = 'block';
            }
        }
    });
}

// Attacher les événements pour une taxe
function attachTaxEventListeners(index) {
    const row = document.querySelector(`.tax-row[data-index="${index}"]`);
    if (!row) return;
    
    row.querySelector('.tax-select').addEventListener('change', updateTotals);
    row.querySelector('.tax-apply-on').addEventListener('change', updateTotals);
    
    row.querySelector('.remove-tax').addEventListener('click', function() {
        if (confirm('Voulez-vous supprimer cette taxe ?')) {
            row.remove();
            updateTotals();
            
            if (document.querySelectorAll('.tax-row').length === 0) {
                document.getElementById('noTaxesAlert').style.display = 'block';
            }
        }
    });
}

// Mettre à jour tous les calculs
function updateTotals() {
    let subtotalProducts = 0;
    let subtotalServices = 0;
    
    // Calculer les totaux par type d'article
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
        const type = row.querySelector('.item-type').value;
        
        const lineTotal = quantity * unitPrice;
        
        // Afficher le total de la ligne
        row.querySelector('.item-total').textContent = formatCurrency(lineTotal);
        
        // Additionner selon le type
        if (type === 'produit') {
            subtotalProducts += lineTotal;
        } else if (type === 'service') {
            subtotalServices += lineTotal;
        }
    });
    
    const subtotal = subtotalProducts + subtotalServices;
    
    // Calculer les taxes
    let totalTax = 0;
    const taxesBreakdown = [];
    
    document.querySelectorAll('.tax-row').forEach(row => {
        const taxSelect = row.querySelector('.tax-select');
        const applyOn = row.querySelector('.tax-apply-on').value;
        const taxRate = parseFloat(taxSelect.options[taxSelect.selectedIndex]?.dataset.rate) || 0;
        const taxName = taxSelect.options[taxSelect.selectedIndex]?.text || '';
        
        let taxBase = 0;
        if (applyOn === 'total') {
            taxBase = subtotal;
        } else if (applyOn === 'products') {
            taxBase = subtotalProducts;
        } else if (applyOn === 'services') {
            taxBase = subtotalServices;
        }
        
        const taxAmount = taxBase * taxRate / 100;
        
        // Afficher dans la ligne de taxe
        row.querySelector('.tax-base').textContent = formatCurrency(taxBase);
        row.querySelector('.tax-amount').textContent = formatCurrency(taxAmount);
        
        totalTax += taxAmount;
        
        if (taxName && applyOn) {
            taxesBreakdown.push({
                name: taxName,
                rate: taxRate,
                base: taxBase,
                amount: taxAmount
            });
        }
    });
    
    const total = subtotal + totalTax;
    
    // Mettre à jour l'affichage des totaux
    document.getElementById('subtotalProducts').textContent = formatCurrency(subtotalProducts);
    document.getElementById('subtotalServices').textContent = formatCurrency(subtotalServices);
    document.getElementById('subtotalAmount').textContent = formatCurrency(subtotal);
    document.getElementById('totalTaxAmount').textContent = formatCurrency(totalTax);
    document.getElementById('totalAmount').textContent = formatCurrency(total);
    
    // Afficher le détail des taxes
    const taxesBreakdownContainer = document.getElementById('taxesBreakdown');
    if (taxesBreakdownContainer) {
        taxesBreakdownContainer.innerHTML = '';
        taxesBreakdown.forEach(tax => {
            taxesBreakdownContainer.innerHTML += `
                <div class="d-flex justify-content-between mb-1">
                    <small>${tax.name} (${tax.rate}% sur ${formatCurrency(tax.base)}) :</small>
                    <small>${formatCurrency(tax.amount)}</small>
                </div>
            `;
        });
    }
    
    // Validation du formulaire
    const hasItems = document.querySelectorAll('.item-row').length > 0;
    const allItemsValid = Array.from(document.querySelectorAll('.item-row')).every(row => {
        return row.querySelector('.item-description').value &&
               row.querySelector('.item-type').value &&
               row.querySelector('.item-quantity').value &&
               row.querySelector('.item-unit-price').value;
    });
    
    const allTaxesValid = Array.from(document.querySelectorAll('.tax-row')).every(row => {
        return row.querySelector('.tax-select').value &&
               row.querySelector('.tax-apply-on').value;
    });
    
    const generateBtn = document.getElementById('generateBtn');
    if (generateBtn) {
        generateBtn.disabled = !(hasItems && allItemsValid && allTaxesValid);
    }
}

// Formater les montants en FCFA
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount) + ' FCFA';
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter automatiquement le premier article
    document.getElementById('addItemBtn').click();
    
    // Ajouter automatiquement une taxe TPS sur services
    document.getElementById('addTaxBtn').click();
    
    // Pré-configurer la taxe TPS Standard sur services
    setTimeout(() => {
        const firstTaxRow = document.querySelector('.tax-row[data-index="0"]');
        if (firstTaxRow) {
            const tpsStandard = Array.from(firstTaxRow.querySelector('.tax-select').options)
                .find(option => option.text.includes('TPS Standard'));
            if (tpsStandard) {
                firstTaxRow.querySelector('.tax-select').value = tpsStandard.value;
            }
            firstTaxRow.querySelector('.tax-apply-on').value = 'services';
            updateTotals();
        }
    }, 100);
    
    // Validation des dates
    document.getElementById('invoice_date').addEventListener('change', function() {
        const invoiceDate = new Date(this.value);
        const dueDateInput = document.getElementById('due_date');
        
        if (invoiceDate) {
            dueDateInput.min = this.value;
            
            if (new Date(dueDateInput.value) <= invoiceDate) {
                const newDueDate = new Date(invoiceDate);
                newDueDate.setDate(newDueDate.getDate() + 30);
                dueDateInput.value = newDueDate.toISOString().split('T')[0];
            }
        }
    });
    
    // Gestion du type de facture
    document.getElementById('invoice_type').addEventListener('change', function() {
        const invoiceType = this.value;
        const itemRows = document.querySelectorAll('.item-row');
        
        itemRows.forEach(row => {
            const typeSelect = row.querySelector('.item-type');
            
            // Réinitialiser les options
            typeSelect.innerHTML = '<option value="">Type...</option>';
            
            if (invoiceType === 'produit') {
                typeSelect.innerHTML += '<option value="produit">Produit</option>';
            } else if (invoiceType === 'service') {
                typeSelect.innerHTML += '<option value="service">Service</option>';
            } else if (invoiceType === 'mixte') {
                typeSelect.innerHTML += '<option value="produit">Produit</option>';
                typeSelect.innerHTML += '<option value="service">Service</option>';
            }
            
            // Si un seul type disponible, le sélectionner automatiquement
            if (typeSelect.options.length === 2) {
                typeSelect.selectedIndex = 1;
            }
        });
        
        updateTotals();
    });
});
</script>
@endsection