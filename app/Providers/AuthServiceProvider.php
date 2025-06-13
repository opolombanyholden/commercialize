<?php

namespace App\Providers;

use App\Policies\QuotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Remplacez 'quote' par une classe si vous avez un modèle Quote
        'quote' => QuotePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}