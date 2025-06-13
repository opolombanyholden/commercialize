<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'version',
        'is_active',
        'pricing_plan_id',  // NOUVEAU
        'account_balance',  // NOUVEAU
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'account_balance' => 'decimal:2',  // NOUVEAU
        ];
    }

    // ==========================================
    // RELATIONS EXISTANTES
    // ==========================================

    /**
     * Get the taxes for this user.
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    /**
     * Get only active taxes for this user.
     */
    public function activeTaxes()
    {
        return $this->hasMany(Tax::class)->where('is_active', true);
    }

    /**
     * Relation avec les PDFs protégés
     */
    public function protectedPdfs()
    {
        return $this->hasMany(ProtectedPdf::class);
    }

    /**
     * Relation avec les PDFs non payés
     */
    public function pendingPdfs()
    {
        return $this->protectedPdfs()->where('is_paid', false);
    }

    // ==========================================
    // NOUVELLES RELATIONS POUR LES PLANS TARIFAIRES
    // ==========================================

    /**
     * Relation avec le plan tarifaire
     */
    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Relation avec les PDFs payés
     */
    public function paidPdfs()
    {
        return $this->protectedPdfs()->where('is_paid', true);
    }

    // ==========================================
    // MÉTHODES EXISTANTES (conservées)
    // ==========================================

    /**
     * Check if user has access to a specific version feature
     */
    public function hasVersionAccess($requiredVersion)
    {
        $versions = ['light', 'standard', 'pro', 'enterprise'];
        $userLevel = array_search($this->version, $versions);
        $requiredLevel = array_search($requiredVersion, $versions);
        
        return $userLevel !== false && $requiredLevel !== false && $userLevel >= $requiredLevel;
    }

    /**
     * Get user's full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get formatted version name
     */
    public function getVersionNameAttribute()
    {
        switch($this->version) {
            case 'light':
                return 'Light';
            case 'standard':
                return 'Standard';
            case 'pro':
                return 'Pro';
            case 'enterprise':
                return 'Enterprise';
            default:
                return 'Light';
        }
    }

    // ==========================================
    // NOUVELLES MÉTHODES POUR LA GESTION TARIFAIRE
    // ==========================================

    /**
     * Vérifie si l'utilisateur peut télécharger un PDF (solde suffisant)
     */
    public function canDownloadPdf(): bool
    {
        $plan = $this->pricingPlan ?? $this->getDefaultPlan();
        return $this->account_balance >= $plan->pdf_download_price;
    }

    /**
     * Débite le compte pour un téléchargement
     */
    public function chargeForDownload(): bool
    {
        $plan = $this->pricingPlan ?? $this->getDefaultPlan();
        
        if ($this->account_balance >= $plan->pdf_download_price) {
            $this->decrement('account_balance', $plan->pdf_download_price);
            return true;
        }
        
        return false;
    }

    /**
     * Obtient le plan par défaut si aucun plan n'est assigné
     */
    public function getDefaultPlan()
    {
        return PricingPlan::where('slug', 'light')->first() ?? (object) [
            'name' => 'Light',
            'slug' => 'light',
            'pdf_download_price' => 500,
            'max_quotes_per_month' => 10,
            'max_invoices_per_month' => 5,
        ];
    }

    /**
     * Obtient le plan effectif (assigné ou par défaut)
     */
    public function getEffectivePlan()
    {
        return $this->pricingPlan ?? $this->getDefaultPlan();
    }

    /**
     * Vérifie si l'utilisateur a atteint sa limite mensuelle de devis
     */
    public function hasReachedQuoteLimit(): bool
    {
        $plan = $this->getEffectivePlan();
        
        if (!$plan->max_quotes_per_month) {
            return false; // Illimité
        }
        
        $monthlyQuotes = $this->protectedPdfs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        return $monthlyQuotes >= $plan->max_quotes_per_month;
    }

    /**
     * Vérifie si l'utilisateur a atteint sa limite mensuelle de factures
     */
    public function hasReachedInvoiceLimit(): bool
    {
        $plan = $this->getEffectivePlan();
        
        if (!$plan->max_invoices_per_month) {
            return false; // Illimité
        }
        
        // TODO: Implémenter quand le module factures sera créé
        // $monthlyInvoices = $this->invoices()
        //     ->whereMonth('created_at', now()->month)
        //     ->whereYear('created_at', now()->year)
        //     ->count();
        //     
        // return $monthlyInvoices >= $plan->max_invoices_per_month;
        
        return false; // Temporaire
    }

    /**
     * Obtient les statistiques d'utilisation du mois en cours
     */
    public function getMonthlyUsageStats(): array
    {
        $plan = $this->getEffectivePlan();
        
        $quotesCount = $this->protectedPdfs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $downloadsCount = $this->paidPdfs()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->count();
        
        return [
            'quotes_count' => $quotesCount,
            'quotes_limit' => $plan->max_quotes_per_month,
            'quotes_remaining' => $plan->max_quotes_per_month ? max(0, $plan->max_quotes_per_month - $quotesCount) : null,
            'downloads_count' => $downloadsCount,
            'total_spent_this_month' => $downloadsCount * $plan->pdf_download_price,
            'account_balance' => $this->account_balance,
            'plan_name' => $plan->name ?? 'Light',
        ];
    }

    /**
     * Formate le solde du compte
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->account_balance, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Vérifie si l'utilisateur a une fonctionnalité spécifique
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->getEffectivePlan();
        
        if (is_object($plan) && method_exists($plan, 'hasFeature')) {
            return $plan->hasFeature($feature);
        }
        
        // Fonctionnalités par défaut pour le plan Light
        $defaultFeatures = ['devis_basique', 'factures_simples'];
        return in_array($feature, $defaultFeatures);
    }

    /**
     * Obtient le niveau du plan (pour comparaisons)
     */
    public function getPlanLevel(): int
    {
        $plan = $this->getEffectivePlan();
        
        switch ($plan->slug ?? 'light') {
            case 'light':
                return 1;
            case 'standard':
                return 2;
            case 'premium':
            case 'pro':
                return 3;
            case 'enterprise':
                return 4;
            default:
                return 1;
        }
    }

    /**
     * Vérifie si l'utilisateur peut effectuer une action selon son plan
     */
    public function canPerformAction(string $action): bool
    {
        switch ($action) {
            case 'create_quote':
                return !$this->hasReachedQuoteLimit();
            case 'create_invoice':
                return !$this->hasReachedInvoiceLimit();
            case 'download_pdf':
                return $this->canDownloadPdf();
            case 'access_reports':
                return $this->getPlanLevel() >= 2; // Standard et plus
            case 'api_access':
                return $this->getPlanLevel() >= 3; // Premium et plus
            case 'priority_support':
                return $this->getPlanLevel() >= 3; // Premium et plus
            default:
                return true;
        }
    }

    /**
     * Scope pour les utilisateurs avec un plan spécifique
     */
    public function scopeWithPlan($query, string $planSlug)
    {
        return $query->whereHas('pricingPlan', function ($q) use ($planSlug) {
            $q->where('slug', $planSlug);
        });
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les utilisateurs avec solde positif
     */
    public function scopeWithBalance($query)
    {
        return $query->where('account_balance', '>', 0);
    }

    // ==========================================
    // MÉTHODES D'ÉVÉNEMENTS (BOOT)
    // ==========================================

    /**
     * Boot method pour ajouter des événements
     */
    protected static function boot()
    {
        parent::boot();

        // Assigner le plan Light par défaut lors de la création
        static::creating(function ($user) {
            if (!$user->pricing_plan_id) {
                $lightPlan = PricingPlan::where('slug', 'light')->first();
                if ($lightPlan) {
                    $user->pricing_plan_id = $lightPlan->id;
                }
            }
            
            // Initialiser le solde à 0 si pas défini
            if (!isset($user->account_balance)) {
                $user->account_balance = 0;
            }
        });
    }
}