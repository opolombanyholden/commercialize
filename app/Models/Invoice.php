<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'user_id',
        'invoice_number',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'invoice_date',
        'due_date',
        'items',
        'subtotal',
        'total_tax',
        'total',
        'notes',
        'password',
        'status',
        'payment_method',
        'payment_details',
        'paid_at',
        'downloaded_at',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'items' => 'array',
        'payment_details' => 'array',
        'subtotal' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Relation avec l'utilisateur propriétaire de la facture
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer les factures d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessor pour formater le numéro de facture
     */
    public function getFormattedInvoiceNumberAttribute()
    {
        return 'FACT-' . $this->invoice_number;
    }

    /**
     * Accessor pour obtenir le statut formaté
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'generated' => 'Générée',
            'paid' => 'Payée',
            'downloaded' => 'Téléchargée',
            'cancelled' => 'Annulée',
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Accessor pour obtenir la classe CSS du statut
     */
    public function getStatusClassAttribute()
    {
        $classes = [
            'generated' => 'badge-warning',
            'paid' => 'badge-info',
            'downloaded' => 'badge-success',
            'cancelled' => 'badge-danger',
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Mutator pour s'assurer que les éléments sont bien formatés
     */
    public function setItemsAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['items'] = $value;
        } else {
            $this->attributes['items'] = json_encode($value);
        }
    }

    /**
     * Vérifie si la facture est payée
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'downloaded']);
    }

    /**
     * Vérifie si la facture est téléchargée
     */
    public function isDownloaded(): bool
    {
        return $this->status === 'downloaded';
    }

    /**
     * Vérifie si la facture est en retard (date d'échéance dépassée)
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !$this->isPaid();
    }

    /**
     * Calcule le nombre de jours avant/après l'échéance
     */
    public function getDaysUntilDueAttribute()
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Obtient le montant total formaté
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2, ',', ' ') . ' FCFA';
    }

    /**
     * Obtient le sous-total formaté
     */
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' FCFA';
    }

    /**
     * Obtient le total des taxes formaté
     */
    public function getFormattedTotalTaxAttribute()
    {
        return number_format($this->total_tax, 2, ',', ' ') . ' FCFA';
    }

    /**
     * Génère un numéro de facture unique
     */
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        // Compteur pour ce mois
        $count = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count() + 1;
        
        return $year . $month . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope pour les factures du mois en cours
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
    }

    /**
     * Scope pour les factures de l'année en cours
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('created_at', date('Y'));
    }

    /**
     * Obtient le montant TTC par ligne d'article
     */
    public function getItemsWithTotalAttribute()
    {
        $items = $this->items;
        
        if (is_array($items)) {
            foreach ($items as &$item) {
                $item['total_with_tax'] = ($item['line_total'] ?? 0) + ($item['line_tax'] ?? 0);
                $item['formatted_total'] = number_format($item['total_with_tax'], 2, ',', ' ') . ' FCFA';
            }
        }
        
        return $items;
    }
}