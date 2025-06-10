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
     * Get formatted rate (ex: 18.00%)
     */
    public function getFormattedRateAttribute()
    {
        return number_format($this->rate, 2) . '%';
    }

    /**
     * Get display name with rate
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->formatted_rate . ')';
    }
}