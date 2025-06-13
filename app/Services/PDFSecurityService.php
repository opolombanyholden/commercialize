<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PDFSecurityService
{
    /**
     * Vérifie les tentatives de mot de passe et applique le rate limiting
     */
    public function verifyPasswordAttempts(string $password, int $quoteId): array
    {
        $key = 'password-attempts:' . auth()->id() . ':' . $quoteId;
        
        if (RateLimiter::tooManyAttempts($key, config('pdf.max_attempts_per_minute', 3))) {
            return [
                'success' => false,
                'message' => 'Trop de tentatives. Réessayez dans 5 minutes.'
            ];
        }
        
        // Vérification du mot de passe
        if (!$this->verifyPassword($password, $quoteId)) {
            RateLimiter::hit($key, config('pdf.rate_limit_decay', 300));
            
            Log::warning('Tentative de mot de passe incorrect', [
                'quote_id' => $quoteId,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            
            return [
                'success' => false,
                'message' => 'Mot de passe incorrect'
            ];
        }
        
        // Succès - nettoie le rate limit
        RateLimiter::clear($key);
        
        Log::info('Accès PDF autorisé', [
            'quote_id' => $quoteId,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);
        
        return ['success' => true];
    }
    
    /**
     * Vérifie le mot de passe pour un devis donné
     */
    private function verifyPassword(string $password, int $quoteId): bool
    {
        // TODO: Implémentez votre logique de vérification
        // Par exemple, comparer avec un hash stocké en base
        // ou utiliser une logique métier spécifique
        
        // Exemple temporaire (à remplacer par votre logique)
        $expectedPassword = 'devis' . $quoteId; // Exemple simple
        return $password === $expectedPassword;
    }
    
    /**
     * Retourne les headers de sécurité pour les PDF
     */
    public function getSecurityHeaders(): array
    {
        return config('pdf.security_headers', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
        ]);
    }
    
    /**
     * Génère un hash de sécurité pour le PDF
     */
    public function generateSecurityHash(int $quoteId, int $userId): string
    {
        return hash('sha256', $quoteId . $userId . config('app.key') . now()->format('Y-m-d'));
    }
}