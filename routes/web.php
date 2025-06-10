<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\QuoteController;
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

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Routes pour la gestion des taxes (Étape 2)
    Route::resource('taxes', TaxController::class);
    
    // Route supplémentaire pour toggle du statut
    Route::patch('/taxes/{tax}/toggle-status', [TaxController::class, 'toggleStatus'])
         ->name('taxes.toggle-status');
    
    // Routes pour les devis (Étape 3)
    Route::prefix('devis')->name('quotes.')->group(function () {
        Route::get('/create', [QuoteController::class, 'create'])->name('create');
        Route::post('/generate', [QuoteController::class, 'generate'])->name('generate');
        Route::get('/payments', [QuoteController::class, 'payments'])->name('payments');
        Route::post('/unlock-pdf', [QuoteController::class, 'unlockPdf'])->name('unlock-pdf');
        Route::get('/access/{id}', [QuoteController::class, 'showPasswordForm'])->name('password-form');
        Route::post('/download/{id}', [QuoteController::class, 'downloadWithPassword'])->name('download-with-password');
    });
    
    // Routes pour les factures (Étape 4)
    Route::prefix('factures')->name('invoices.')->group(function () {
        Route::get('/create', function () {
            return view('invoices.create');
        })->name('create');
        
        Route::post('/generate', function () {
            return response()->json(['message' => 'Génération PDF à implémenter']);
        })->name('generate');
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
    
    // Route pour le profil utilisateur (bonus)
    Route::get('/profil', function () {
        return view('profile.show');
    })->name('profile.show');
    
});