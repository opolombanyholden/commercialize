<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ProtectedPdf extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'quote_number',
        'filename',
        'password',
        'pdf_content',
        'quote_data',
        'client_email',
        'client_phone',
        'total_amount',
        'security_hash',
        'is_paid',
        'paid_at',
        'payment_reference',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quote_data' => 'array',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pdf_content',
        'password',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie le mot de passe fourni
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        // Comparaison directe (mot de passe en clair)
        // Vous pouvez utiliser Hash::check() si vous préférez hasher les mots de passe
        return $this->password === $password;
    }

    /**
     * Marque le PDF comme payé
     *
     * @param string|null $paymentReference
     * @return bool
     */
    public function markAsPaid(string $paymentReference = null): bool
    {
        $this->is_paid = true;
        $this->paid_at = now();
        
        if ($paymentReference) {
            $this->payment_reference = $paymentReference;
        }
        
        return $this->save();
    }

    /**
     * Scope pour les PDFs non payés
     */
    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Scope pour les PDFs payés
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accesseur pour obtenir le statut de paiement en français
     */
    public function getPaymentStatusAttribute(): string
    {
        return $this->is_paid ? 'Payé' : 'En attente';
    }

    /**
     * Accesseur pour obtenir la taille du fichier PDF
     */
    public function getPdfSizeAttribute(): string
    {
        if (empty($this->pdf_content)) {
            return '0 KB';
        }
        
        $bytes = strlen(base64_decode($this->pdf_content));
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Mutateur pour hasher le mot de passe si nécessaire
     * (Optionnel - décommentez si vous voulez hasher les mots de passe)
     */
    /*
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }
    */

    /**
     * Méthode pour obtenir l'URL d'accès au PDF
     */
    public function getAccessUrlAttribute(): string
    {
        return route('quotes.password-form', $this->id);
    }

    /**
     * Méthode pour vérifier si le PDF a expiré (optionnel)
     */
    public function isExpired(int $daysValid = 30): bool
    {
        return $this->created_at->addDays($daysValid)->isPast();
    }

    /**
     * Boot method pour ajouter des événements
     */
    protected static function boot()
    {
        parent::boot();

        // Événement avant création
        static::creating(function ($protectedPdf) {
            // Générer un hash de sécurité si pas fourni
            if (empty($protectedPdf->security_hash)) {
                $protectedPdf->security_hash = hash('sha256', 
                    $protectedPdf->quote_number . 
                    $protectedPdf->user_id . 
                    now()->timestamp
                );
            }
        });
    }
}