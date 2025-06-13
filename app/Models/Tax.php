<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'rate',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this tax.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate tax amount for a given price (excluding tax)
     * Supporte maintenant les taux négatifs (remises)
     */
    public function calculateTax($amountExcludingTax)
    {
        return round($amountExcludingTax * ($this->rate / 100), 2);
    }

    /**
     * Calculate price including tax
     */
    public function calculatePriceWithTax($amountExcludingTax)
    {
        return $amountExcludingTax + $this->calculateTax($amountExcludingTax);
    }

    /**
     * Calculate price excluding tax from price including tax
     */
    public function calculatePriceExcludingTax($amountIncludingTax)
    {
        return round($amountIncludingTax / (1 + $this->rate / 100), 2);
    }

    /**
     * Scope a query to only include active taxes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include taxes for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get formatted rate with sign (ex: +18.00% or -5.00%)
     */
    public function getFormattedRateAttribute()
    {
        $sign = $this->rate >= 0 ? '+' : '';
        return $sign . number_format($this->rate, 2) . '%';
    }

    /**
     * Get display name with rate (for forms and lists)
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->formatted_rate . ')';
    }

    /**
     * Get full label for PDF and detailed views
     */
    public function getFullLabelAttribute()
    {
        $sign = $this->rate >= 0 ? '+' : '';
        $typeLabel = $this->isDiscount ? 'Remise' : 'Taxe';
        
        return $this->name . ' (' . $typeLabel . ' : ' . $sign . number_format($this->rate, 2) . '%)';
    }

    /**
     * Determine if this is a discount (negative rate)
     */
    public function getIsDiscountAttribute()
    {
        return $this->rate < 0;
    }

    /**
     * Get the type label in French
     */
    public function getTypeLabelAttribute()
    {
        return $this->isDiscount ? 'Remise' : 'Taxe';
    }

    /**
     * Get rate with proper styling for display
     */
    public function getStyledRateAttribute()
    {
        $class = $this->isDiscount ? 'text-green-600' : 'text-blue-600';
        $sign = $this->rate >= 0 ? '+' : '';
        
        return [
            'value' => $sign . number_format($this->rate, 2) . '%',
            'class' => $class,
            'type' => $this->type_label
        ];
    }

    /**
     * Scope for taxes only (positive rates)
     */
    public function scopeTaxesOnly($query)
    {
        return $query->where('rate', '>=', 0);
    }

    /**
     * Scope for discounts only (negative rates)
     */
    public function scopeDiscountsOnly($query)
    {
        return $query->where('rate', '<', 0);
    }
}