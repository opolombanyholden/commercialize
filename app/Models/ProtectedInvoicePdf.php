<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProtectedInvoicePdf extends Model
{
    use HasFactory;

    protected $table = 'protected_invoice_pdfs';

    protected $fillable = [
        'user_id',
        'invoice_number',
        'formatted_invoice_number',
        'hash',
        'filename',
        'password',
        'password_hash',
        'password_expires_at',
        'pdf_content',
        'invoice_data',
        'client_email',
        'client_phone',
        'total_amount',
        'currency',
        'security_hash',
        'is_paid',
        'paid_at',
        'payment_reference',
        'payment_method',
        'payment_notes',
        'notification_method',
        'client_contact',
        'due_date',
        'download_count',
        'last_downloaded_at'
    ];

    protected $casts = [
        'invoice_data' => 'array',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
        'password_expires_at' => 'datetime',
        'download_count' => 'integer',
        'last_downloaded_at' => 'datetime'
    ];

    protected $hidden = [
        'password',
        'password_hash',
        'pdf_content',
        'security_hash',
    ];

    protected $dates = [
        'due_date',
        'paid_at',
        'password_expires_at',
        'last_downloaded_at'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Générer un nom de fichier unique pour la facture
     */
    public static function generateFilename($invoiceNumber): string
    {
        return 'facture_' . $invoiceNumber . '_' . time() . '.pdf';
    }

    /**
     * Générer un hash de sécurité unique
     */
    public static function generateSecurityHash($invoiceNumber, $clientEmail): string
    {
        return hash('sha256', $invoiceNumber . $clientEmail . config('app.key') . time());
    }

    /**
     * Générer un hash unique pour l'accès public
     */
    public static function generateUniqueHash(): string
    {
        do {
            $hash = \Illuminate\Support\Str::random(32);
        } while (self::where('hash', $hash)->exists());

        return $hash;
    }

    /**
     * Vérifier si la facture est en retard
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !$this->is_paid;
    }

    /**
     * Vérifier si le mot de passe a expiré
     */
    public function isPasswordExpired(): bool
    {
        return $this->password_expires_at && now()->gt($this->password_expires_at);
    }

    /**
     * Obtenir le nombre de jours avant/après l'échéance
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Formater le montant total
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' ' . ($this->currency ?? 'FCFA');
    }

    /**
     * Sous-total formaté (depuis invoice_data)
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $subtotal = $this->invoice_data['totals']['subtotal'] ?? 0;
        return number_format($subtotal, 0, ',', ' ') . ' ' . ($this->currency ?? 'FCFA');
    }

    /**
     * Total des taxes formaté (depuis invoice_data)
     */
    public function getFormattedTotalTaxAttribute(): string
    {
        $totalTax = $this->invoice_data['totals']['total_tax'] ?? 0;
        return number_format($totalTax, 0, ',', ' ') . ' ' . ($this->currency ?? 'FCFA');
    }

    /**
     * Obtenir le numéro de facture formaté
     */
    public function getFormattedInvoiceNumberAttribute(): string
    {
        return $this->formatted_invoice_number ?? ('FACT-' . $this->invoice_number);
    }

    /**
     * Obtenir le statut de la facture
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_paid) {
            return 'paid';
        } elseif ($this->isOverdue()) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute(): string
    {
        switch ($this->status) {
            case 'paid':
                return 'Payée';
            case 'overdue':
                return 'En retard';
            case 'pending':
                return 'En attente';
            default:
                return 'Inconnu';
        }
    }

    /**
     * Obtenir la classe CSS du statut
     */
    public function getStatusClassAttribute(): string
    {
        switch ($this->status) {
            case 'paid':
                return 'bg-success text-white';
            case 'overdue':
                return 'bg-danger text-white';
            case 'pending':
                return 'bg-warning text-dark';
            default:
                return 'bg-secondary text-white';
        }
    }

    /**
     * Générer un numéro de facture formaté
     */
    public static function generateInvoiceNumber(): array
    {
        $year = date('Y');
        $month = date('m');
        
        // Compter les factures du mois
        $count = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        $number = $count;
        $formatted = sprintf('FAC-%s-%s-%04d', $year, $month, $count);

        return [
            'number' => $number,
            'formatted' => $formatted
        ];
    }

    /**
     * Créer une nouvelle facture protégée
     */
    public static function createFromInvoiceData(array $invoiceData, int $userId): self
    {
        $invoiceNumbers = self::generateInvoiceNumber();
        
        return self::create([
            'user_id' => $userId,
            'invoice_number' => $invoiceNumbers['number'],
            'formatted_invoice_number' => $invoiceNumbers['formatted'],
            'hash' => self::generateUniqueHash(),
            'invoice_data' => $invoiceData,
            'total_amount' => $invoiceData['totals']['total'] ?? 0,
            'currency' => $invoiceData['invoice']['currency'] ?? 'FCFA',
            'due_date' => Carbon::parse($invoiceData['invoice']['due_date'] ?? now()->addDays(30)),
            'is_paid' => false,
            'download_count' => 0,
            'client_email' => $invoiceData['client']['email'] ?? null,
            'client_phone' => $invoiceData['client']['phone'] ?? null
        ]);
    }

    /**
     * Marquer comme payée
     */
    public function markAsPaid(string $paymentMethod, ?string $paymentReference = null, ?string $notes = null): self
    {
        $this->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'payment_notes' => $notes
        ]);

        return $this;
    }

    /**
     * Définir le mot de passe
     */
    public function setPassword(string $password, int $hoursValid = 24): self
    {
        $this->update([
            'password_hash' => \Illuminate\Support\Facades\Hash::make($password),
            'password_expires_at' => now()->addHours($hoursValid)
        ]);

        return $this;
    }

    /**
     * Vérifier le mot de passe
     */
    public function checkPassword(string $password): bool
    {
        if (!$this->password_hash) {
            return false;
        }

        if ($this->isPasswordExpired()) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check($password, $this->password_hash);
    }

    /**
     * Incrémenter le nombre de téléchargements
     */
    public function incrementDownloads(): self
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);

        return $this;
    }

    /**
     * Scope pour les factures d'un utilisateur
     */
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les factures payées
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope pour les factures en attente
     */
    public function scopePending($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Scope pour les factures en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_paid', false)
                    ->where('due_date', '<', now());
    }
}