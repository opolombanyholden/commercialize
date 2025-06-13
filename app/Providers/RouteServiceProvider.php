<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\ProtectedInvoicePdf;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // === ROUTE MODEL BINDING PERSONNALISÉ ===
        
        // Binding pour ProtectedInvoicePdf - Permet d'utiliser ID ou hash
        Route::bind('protectedInvoice', function ($value) {
            // Si c'est un nombre, recherche par ID avec vérification du propriétaire
            if (is_numeric($value)) {
                // Vérification que l'utilisateur est propriétaire de la facture
                if (auth()->check()) {
                    return ProtectedInvoicePdf::where('user_id', auth()->id())
                        ->findOrFail($value);
                }
                
                // Si pas authentifié, chercher par ID uniquement (pour admin par exemple)
                return ProtectedInvoicePdf::findOrFail($value);
            }
            
            // Si c'est un hash, recherche directe (accès public)
            return ProtectedInvoicePdf::where('hash', $value)->firstOrFail();
        });

        // Binding additionnel pour les routes publiques avec hash uniquement
        Route::bind('invoiceHash', function ($value) {
            return ProtectedInvoicePdf::where('hash', $value)->firstOrFail();
        });

        // Binding pour les quotes si nécessaire (compatibilité)
        Route::bind('quote', function ($value) {
            if (class_exists('App\Models\Quote')) {
                if (is_numeric($value)) {
                    return \App\Models\Quote::where('user_id', auth()->id())
                        ->findOrFail($value);
                }
                return \App\Models\Quote::where('hash', $value)->firstOrFail();
            }
            
            abort(404);
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiting pour les tentatives de connexion
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip())->response(function () {
                return response()->json([
                    'message' => 'Trop de tentatives de connexion. Réessayez dans quelques minutes.'
                ], 429);
            });
        });

        // Rate limiting pour les API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user() ? $request->user()->id : $request->ip());
        });

        // Rate limiting spécifique pour les téléchargements de factures
        RateLimiter::for('invoice-download', function (Request $request) {
            return [
                // Maximum 10 téléchargements par minute par IP
                Limit::perMinute(10)->by($request->ip()),
                // Maximum 50 téléchargements par heure par IP
                Limit::perHour(50)->by($request->ip())
            ];
        });

        // Rate limiting pour les tentatives de mot de passe incorrect
        RateLimiter::for('password-attempts', function (Request $request) {
            $key = 'password-attempts:' . $request->ip() . ':' . $request->route('protectedInvoice');
            
            return [
                // Maximum 5 tentatives par minute
                Limit::perMinute(5)->by($key)->response(function () {
                    return back()->withErrors([
                        'password' => 'Trop de tentatives incorrectes. Attendez quelques minutes avant de réessayer.'
                    ]);
                }),
                // Maximum 20 tentatives par heure
                Limit::perHour(20)->by($key)->response(function () {
                    return back()->withErrors([
                        'password' => 'Limite d\'essais dépassée pour cette heure. Contactez le support si nécessaire.'
                    ]);
                })
            ];
        });

        // Rate limiting pour la génération de factures
        RateLimiter::for('invoice-generation', function (Request $request) {
            $userId = $request->user() ? $request->user()->id : $request->ip();
            
            return [
                // Maximum 5 générations par minute par utilisateur
                Limit::perMinute(5)->by($userId),
                // Maximum 100 générations par jour par utilisateur
                Limit::perDay(100)->by($userId)
            ];
        });

        // Rate limiting pour les webhooks de paiement
        RateLimiter::for('payment-webhook', function (Request $request) {
            return [
                // Maximum 100 webhooks par minute (pour gérer les pics)
                Limit::perMinute(100)->by($request->ip()),
                // Maximum 1000 webhooks par heure
                Limit::perHour(1000)->by($request->ip())
            ];
        });
    }
}

/*
|--------------------------------------------------------------------------
| Utilisation dans les routes
|--------------------------------------------------------------------------
|
| Pour utiliser le rate limiting dans vos routes, ajoutez le middleware :
|
| Route::middleware(['throttle:invoice-download'])->group(function () {
|     // Routes de téléchargement
| });
|
| Route::middleware(['throttle:password-attempts'])->group(function () {
|     // Routes de validation de mot de passe
| });
|
*/