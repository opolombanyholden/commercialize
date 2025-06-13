<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\PricingPlanController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProtectedInvoicePdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes CommercialiZe
|--------------------------------------------------------------------------
*/

// Redirection racine vers login
Route::get('/', function () {
    return redirect()->route('login');
});

// Routes d'authentification (accessibles aux invités uniquement)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Route de déconnexion (accessible aux utilisateurs connectés)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// === ROUTES PUBLIQUES POUR ACCÈS SÉCURISÉ AUX FACTURES ===
// Ces routes ne nécessitent pas d'authentification car elles utilisent le hash sécurisé

// Accès public à une facture protégée via hash
Route::get('/protected-invoice/{hash}', [ProtectedInvoicePdfController::class, 'show'])
    ->name('protected-invoices.show');

// Téléchargement public d'une facture via hash (affiche le formulaire de mot de passe)
Route::get('/protected-invoice/{hash}/download', [ProtectedInvoicePdfController::class, 'download'])
    ->name('protected-invoices.download');

// Validation du mot de passe et téléchargement du PDF
Route::post('/protected-invoice/{hash}/validate-password', [ProtectedInvoicePdfController::class, 'validatePasswordAndDownload'])
    ->name('protected-invoices.validate-password');

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Routes pour la gestion des taxes (Étape 2)
    Route::resource('taxes', TaxController::class);
    
    // Route supplémentaire pour toggle du statut
    Route::patch('/taxes/{tax}/toggle-status', [TaxController::class, 'toggleStatus'])
         ->name('taxes.toggle-status');
    
    // Route AJAX pour récupérer les taxes dans les formulaires
    Route::get('/taxes/for-select', [TaxController::class, 'getTaxesForSelect'])
         ->name('taxes.for-select');

    // Routes pour les devis (Étape 3) - WORKFLOW COMPLET
    Route::prefix('devis')->name('quotes.')->group(function () {
        // Création et génération de devis
        Route::get('/create', [QuoteController::class, 'create'])->name('create');
        Route::post('/generate', [QuoteController::class, 'generate'])->name('generate');
        
        // Téléchargement des instructions de paiement
        Route::get('/download-instructions', [QuoteController::class, 'downloadInstructions'])->name('download-instructions');

        // Gestion des paiements centralisée
        Route::get('/payments', [QuoteController::class, 'payments'])->name('payments');
        Route::post('/process-payment', [QuoteController::class, 'processPayment'])->name('process-payment');

        // Accès client au PDF avec mot de passe
        Route::get('/access/{id}', [QuoteController::class, 'showPasswordForm'])
             ->name('password-form')
             ->where('id', '[0-9]+');
        Route::post('/download/{id}', [QuoteController::class, 'downloadWithPassword'])
             ->name('download-with-password')
             ->where('id', '[0-9]+');
    });

    // === ROUTES POUR LES FACTURES (Étape 4) - SYSTÈME PROTÉGÉ ===
    Route::prefix('factures')->name('invoices.')->group(function () {
        
        // === CRÉATION ET GÉNÉRATION ===
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/generate', [InvoiceController::class, 'generate'])->name('generate');
        
        // === GESTION DES PAIEMENTS ===
        // Page de paiement pour une facture spécifique ou la plus récente
        Route::get('/payments/{id?}', [InvoiceController::class, 'payments'])
            ->name('payments')
            ->where('id', '[0-9]+');
        
        // Traitement du paiement
        Route::post('/process-payment', [InvoiceController::class, 'processPayment'])
            ->name('process-payment');
        
        // === INSTRUCTIONS DE TÉLÉCHARGEMENT ===
        // Page d'instructions (vue web)
        Route::get('/download-instructions/{id}', [InvoiceController::class, 'downloadInstructions'])
            ->name('download-instructions')
            ->where('id', '[0-9]+');
        
        // Téléchargement du PDF d'instructions
        Route::get('/download-instructions-pdf/{id}', [InvoiceController::class, 'downloadInstructionsPdf'])
            ->name('download-instructions-pdf')
            ->where('id', '[0-9]+');
        
        // === ACCÈS AVEC MOT DE PASSE (POUR UTILISATEURS CONNECTÉS) ===
        // Formulaire de saisie du mot de passe
        Route::get('/access/{id}', [InvoiceController::class, 'showPasswordForm'])
            ->name('password-form')
            ->where('id', '[0-9]+');
        
        // Téléchargement avec validation du mot de passe
        Route::post('/download/{id}', [InvoiceController::class, 'downloadWithPassword'])
            ->name('download-with-password')
            ->where('id', '[0-9]+');
        
        // === GESTION ADMINISTRATIVE ===
        // Liste des factures
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        
        // Détails d'une facture
        Route::get('/{id}', [InvoiceController::class, 'show'])
            ->name('show')
            ->where('id', '[0-9]+');
        
        // Édition d'une facture (si pas encore payée)
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])
            ->name('edit')
            ->where('id', '[0-9]+');
        
        // Mise à jour d'une facture
        Route::put('/{id}', [InvoiceController::class, 'update'])
            ->name('update')
            ->where('id', '[0-9]+');
        
        // Suppression d'une facture (si pas encore payée)
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])
            ->name('destroy')
            ->where('id', '[0-9]+');
    });

    // === ROUTES ADMINISTRATIVES POUR LES FACTURES PROTÉGÉES ===
    Route::prefix('protected-invoices')->name('protected-invoices.')->group(function () {
        
        // Liste des factures protégées
        Route::get('/', [ProtectedInvoicePdfController::class, 'index'])->name('index');
        
        // Détails administratifs d'une facture protégée
        Route::get('/{id}', [ProtectedInvoicePdfController::class, 'showAdmin'])
            ->name('show-admin')
            ->where('id', '[0-9]+');
        
        // Mise à jour du statut de paiement
        Route::patch('/{id}/payment-status', [ProtectedInvoicePdfController::class, 'updatePaymentStatus'])
            ->name('update-payment-status')
            ->where('id', '[0-9]+');
        
        // Régénération du mot de passe
        Route::post('/{id}/regenerate-password', [ProtectedInvoicePdfController::class, 'regeneratePassword'])
            ->name('regenerate-password')
            ->where('id', '[0-9]+');
        
        // Envoi du mot de passe
        Route::post('/{id}/send-password', [ProtectedInvoicePdfController::class, 'sendPassword'])
            ->name('send-password')
            ->where('id', '[0-9]+');
        
        // Marquer comme payé manuellement
        Route::post('/{id}/mark-as-paid', [ProtectedInvoicePdfController::class, 'markAsPaid'])
            ->name('mark-as-paid')
            ->where('id', '[0-9]+');
    });

    // Routes pour la gestion des plans tarifaires
    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('/', [PricingPlanController::class, 'index'])->name('index');
        Route::get('/plans', [PricingPlanController::class, 'showPlans'])->name('plans');
        Route::post('/change-plan', [PricingPlanController::class, 'changePlan'])->name('change-plan');
        Route::get('/billing', [PricingPlanController::class, 'billing'])->name('billing');
        Route::post('/add-credits', [PricingPlanController::class, 'addCredits'])->name('add-credits');
    });
    
    // Routes pour les bons de livraison (Étape 5)
    Route::prefix('livraisons')->name('deliveries.')->group(function () {
        Route::get('/create', function () {
            return view('deliveries.create');
        })->name('create');
        
        Route::post('/generate', function () {
            return response()->json(['message' => 'Génération PDF à implémenter']);
        })->name('generate');
    });
    
    // Routes pour la gestion du profil utilisateur
    Route::prefix('profil')->name('profile.')->group(function () {
        Route::get('/', function () {
            return view('profile.show');
        })->name('show');
        
        Route::get('/edit', function () {
            return view('profile.edit');
        })->name('edit');
        
        Route::patch('/update', function () {
            return redirect()->route('profile.show')->with('success', 'Profil mis à jour');
        })->name('update');
    });

    // Routes pour les rapports et statistiques (BONUS)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/quotes', function () {
            return view('reports.quotes');
        })->name('quotes');
        
        Route::get('/revenue', function () {
            return view('reports.revenue');
        })->name('revenue');
        
        Route::get('/clients', function () {
            return view('reports.clients');
        })->name('clients');
    });

    // Routes administratives (pour les super-utilisateurs)
    Route::prefix('admin')->name('admin.')->middleware('can:admin-access')->group(function () {
        Route::resource('pricing-plans', PricingPlanController::class);
        Route::get('/users', function () {
            return view('admin.users.index');
        })->name('users.index');
        Route::get('/system', function () {
            return view('admin.system.index');
        })->name('system.index');
    });
    
});

// === ROUTES API (optionnel pour webhooks et intégrations) ===
Route::prefix('api')->name('api.')->group(function () {
    
    // Webhook pour notifications de paiement
    Route::post('/payment-webhook', [ProtectedInvoicePdfController::class, 'handlePaymentWebhook'])
        ->name('payment-webhook');
    
    // API pour vérifier le statut d'une facture (via hash)
    Route::get('/protected-invoice/{hash}/status', [ProtectedInvoicePdfController::class, 'getStatus'])
        ->name('get-invoice-status');
    
    // Routes API protégées
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Statistiques des factures
        Route::get('/invoices/stats', [ProtectedInvoicePdfController::class, 'getStats'])
            ->name('invoice-stats');
        
        // Export des factures
        Route::get('/invoices/export', [ProtectedInvoicePdfController::class, 'export'])
            ->name('export-invoices');
    });
});