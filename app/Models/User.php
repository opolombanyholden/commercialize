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
        ];
    }

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
}