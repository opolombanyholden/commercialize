<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotePolicy
{
    /**
     * Détermine si l'utilisateur peut créer des devis
     */
    public function create(User $user): bool
    {
        // Tous les utilisateurs connectés peuvent créer des devis
        return true;
    }
    
    /**
     * Détermine si l'utilisateur peut accéder à un devis
     */
    public function access(User $user, string $quoteId): bool
    {
        // Pour l'instant, tous les utilisateurs connectés peuvent accéder
        return true;
    }
    
    /**
     * Détermine si l'utilisateur peut télécharger un devis
     */
    public function download(User $user, string $quoteId): bool
    {
        return $this->access($user, $quoteId);
    }
}