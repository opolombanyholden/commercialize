<?php

namespace App\Http\Controllers;

use App\Models\ProtectedPdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ProtectedPdfController extends Controller
{
    /**
     * Afficher le formulaire de saisie du mot de passe
     */
    public function show(string $token)
    {
        $pdf = ProtectedPdf::active()->where('token', $token)->first();
        
        if (!$pdf) {
            abort(404, 'PDF non trouvé ou expiré');
        }

        return view('pdf.protected', [
            'pdf' => $pdf->getPublicInfo()
        ]);
    }

    /**
     * Vérifier le mot de passe et télécharger le PDF
     */
    public function download(Request $request, string $token)
    {
        // Rate limiting pour éviter les attaques par force brute
        $key = 'pdf-download:' . $request->ip() . ':' . $token;
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'password' => ["Trop de tentatives. Réessayez dans {$seconds} secondes."]
            ]);
        }

        $request->validate([
            'password' => 'required|string'
        ]);

        $pdf = ProtectedPdf::active()->where('token', $token)->first();
        
        if (!$pdf) {
            abort(404, 'PDF non trouvé ou expiré');
        }

        if (!$pdf->canDownload()) {
            return back()->withErrors([
                'general' => 'Ce PDF n\'est plus disponible au téléchargement.'
            ]);
        }

        if (!$pdf->verifyPassword($request->password)) {
            RateLimiter::hit($key, 300); // 5 minutes de blocage
            
            return back()->withErrors([
                'password' => 'Mot de passe incorrect.'
            ])->withInput();
        }

        // Mot de passe correct, obtenir le contenu
        $content = $pdf->getContent();
        
        if (!$content) {
            Log::error("Impossible de récupérer le contenu du PDF", [
                'token' => $token,
                'stored_path' => $pdf->stored_path
            ]);
            
            return back()->withErrors([
                'general' => 'Erreur lors de la récupération du fichier.'
            ]);
        }

        // Réinitialiser le rate limiting en cas de succès
        RateLimiter::clear($key);

        // Log du téléchargement
        Log::info("PDF protégé téléchargé", [
            'token' => $token,
            'filename' => $pdf->filename,
            'ip' => $request->ip(),
            'downloads_remaining' => max(0, $pdf->max_downloads - $pdf->download_count)
        ]);

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $pdf->filename . '"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    /**
     * API pour obtenir les informations du PDF
     */
    public function info(string $token)
    {
        $pdf = ProtectedPdf::active()->where('token', $token)->first();
        
        if (!$pdf) {
            return response()->json(['error' => 'PDF non trouvé'], 404);
        }

        return response()->json($pdf->getPublicInfo());
    }

    /**
     * Prévisualisation (sans téléchargement)
     */
    public function preview(Request $request, string $token)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $pdf = ProtectedPdf::active()->where('token', $token)->first();
        
        if (!$pdf || !$pdf->verifyPassword($request->password)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Retourner juste les métadonnées pour prévisualisation
        return response()->json([
            'filename' => $pdf->filename,
            'metadata' => $pdf->metadata,
            'size' => strlen($pdf->getContent()),
            'can_download' => $pdf->canDownload(),
            'downloads_remaining' => max(0, $pdf->max_downloads - $pdf->download_count)
        ]);
    }
}