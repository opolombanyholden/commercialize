<?php

namespace App\Http\Controllers;

use App\Models\ProtectedInvoicePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProtectedInvoicePdfController extends Controller
{
    /**
     * Liste des factures protégées pour l'utilisateur connecté
     */
    public function index()
    {
        $protectedInvoices = ProtectedInvoicePdf::where('user_id', auth()->id())
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('protected-invoices.index', compact('protectedInvoices'));
    }
    
    /**
     * Affichage public d'une facture via hash (formulaire de mot de passe)
     */
    public function show($hash)
    {
        $protectedInvoice = ProtectedInvoicePdf::where('hash', $hash)->firstOrFail();
        
        return view('password-form', compact('protectedInvoice'));
    }
    
    /**
     * Page de téléchargement public (même que show)
     */
    public function download($hash)
    {
        return $this->show($hash);
    }
    
    /**
     * Validation du mot de passe et téléchargement du PDF
     */
    public function validatePasswordAndDownload(Request $request, $hash)
    {
        $request->validate([
            'password' => 'required|string|min:6'
        ], [
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.'
        ]);
        
        $protectedInvoice = ProtectedInvoicePdf::where('hash', $hash)->firstOrFail();
        
        // Vérification du mot de passe
        if (!Hash::check($request->password, $protectedInvoice->password_hash)) {
            Log::warning('Tentative d\'accès avec mot de passe incorrect', [
                'invoice_id' => $protectedInvoice->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return back()->withErrors(['password' => 'Mot de passe incorrect. Veuillez vérifier et réessayer.']);
        }
        
        // Vérification de l'expiration du mot de passe
        if ($protectedInvoice->password_expires_at && now()->gt($protectedInvoice->password_expires_at)) {
            return back()->withErrors([
                'password' => 'Ce mot de passe a expiré le ' . $protectedInvoice->password_expires_at->format('d/m/Y à H:i') . '. Contactez l\'émetteur pour obtenir un nouveau mot de passe.'
            ]);
        }
        
        // Enregistrement de l'accès réussi
        $protectedInvoice->increment('download_count');
        $protectedInvoice->update(['last_downloaded_at' => now()]);
        
        Log::info('Téléchargement de facture réussi', [
            'invoice_id' => $protectedInvoice->id,
            'download_count' => $protectedInvoice->download_count,
            'ip' => $request->ip()
        ]);
        
        // Génération et téléchargement du PDF
        try {
            $pdf = Pdf::loadView('pdf', compact('protectedInvoice'))
                ->setPaper('a4')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true
                ]);
            
            $filename = $protectedInvoice->formatted_invoice_number . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF', [
                'invoice_id' => $protectedInvoice->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['password' => 'Erreur lors de la génération du PDF. Veuillez réessayer ou contacter le support.']);
        }
    }
    
    /**
     * Affichage administratif d'une facture protégée
     */
    public function showAdmin(ProtectedInvoicePdf $protectedInvoice)
    {
        // Vérification que la facture appartient à l'utilisateur connecté
        if ($protectedInvoice->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette facture.');
        }
        
        return view('protected-invoices.show', compact('protectedInvoice'));
    }
    
    /**
     * Mise à jour du statut de paiement
     */
    public function updatePaymentStatus(Request $request, ProtectedInvoicePdf $protectedInvoice)
    {
        // Vérification que la facture appartient à l'utilisateur connecté
        if ($protectedInvoice->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette facture.');
        }
        
        $request->validate([
            'is_paid' => 'required|boolean',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'paid_at' => 'nullable|date'
        ]);
        
        $updateData = [
            'is_paid' => $request->is_paid,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
        ];
        
        if ($request->is_paid && $request->paid_at) {
            $updateData['paid_at'] = Carbon::parse($request->paid_at);
        } elseif ($request->is_paid && !$protectedInvoice->paid_at) {
            $updateData['paid_at'] = now();
        } elseif (!$request->is_paid) {
            $updateData['paid_at'] = null;
        }
        
        $protectedInvoice->update($updateData);
        
        Log::info('Statut de paiement mis à jour', [
            'invoice_id' => $protectedInvoice->id,
            'is_paid' => $request->is_paid,
            'updated_by' => auth()->id()
        ]);
        
        return back()->with('success', 'Statut de paiement mis à jour avec succès.');
    }
    
    /**
     * Régénération du mot de passe
     */
    public function regeneratePassword(ProtectedInvoicePdf $protectedInvoice)
    {
        // Vérification que la facture appartient à l'utilisateur connecté
        if ($protectedInvoice->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette facture.');
        }
        
        // Génération d'un nouveau mot de passe
        $newPassword = $this->generateSecurePassword();
        
        $protectedInvoice->update([
            'password_hash' => Hash::make($newPassword),
            'password_expires_at' => now()->addHours(24)
        ]);
        
        Log::info('Mot de passe régénéré', [
            'invoice_id' => $protectedInvoice->id,
            'regenerated_by' => auth()->id()
        ]);
        
        return back()->with([
            'success' => 'Nouveau mot de passe généré avec succès. Valable 24h.',
            'password_hint' => $newPassword // Pour debug uniquement en développement
        ]);
    }
    
    /**
     * Envoi du mot de passe par notification
     */
    public function sendPassword(Request $request, ProtectedInvoicePdf $protectedInvoice)
    {
        // Vérification que la facture appartient à l'utilisateur connecté
        if ($protectedInvoice->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette facture.');
        }
        
        $request->validate([
            'notification_method' => 'required|in:email,sms',
            'contact' => 'required|string'
        ]);
        
        // TODO: Implémenter l'envoi réel par email ou SMS
        // Pour l'instant, simulation
        
        $method = $request->notification_method === 'email' ? 'email' : 'SMS';
        
        Log::info('Mot de passe envoyé', [
            'invoice_id' => $protectedInvoice->id,
            'method' => $method,
            'contact' => $request->contact,
            'sent_by' => auth()->id()
        ]);
        
        return back()->with('success', "Mot de passe envoyé par {$method} avec succès.");
    }
    
    /**
     * Marquer une facture comme payée manuellement
     */
    public function markAsPaid(Request $request, ProtectedInvoicePdf $protectedInvoice)
    {
        // Vérification que la facture appartient à l'utilisateur connecté
        if ($protectedInvoice->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette facture.');
        }
        
        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $protectedInvoice->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_notes' => $request->notes
        ]);
        
        // Génération d'un mot de passe si pas encore fait
        if (!$protectedInvoice->password_hash) {
            $newPassword = $this->generateSecurePassword();
            $protectedInvoice->update([
                'password_hash' => Hash::make($newPassword),
                'password_expires_at' => now()->addHours(24)
            ]);
        }
        
        Log::info('Facture marquée comme payée manuellement', [
            'invoice_id' => $protectedInvoice->id,
            'payment_method' => $request->payment_method,
            'marked_by' => auth()->id()
        ]);
        
        return back()->with('success', 'Facture marquée comme payée avec succès.');
    }
    
    /**
     * Webhook pour traiter les notifications de paiement automatique
     */
    public function handlePaymentWebhook(Request $request)
    {
        // TODO: Implémenter la gestion des webhooks de paiement
        // (Mobile Money, PayPal, Stripe, etc.)
        
        Log::info('Webhook de paiement reçu', [
            'payload' => $request->all(),
            'ip' => $request->ip()
        ]);
        
        return response()->json(['status' => 'received'], 200);
    }
    
    /**
     * API pour obtenir le statut d'une facture
     */
    public function getStatus($hash)
    {
        $protectedInvoice = ProtectedInvoicePdf::where('hash', $hash)->firstOrFail();
        
        return response()->json([
            'invoice_number' => $protectedInvoice->formatted_invoice_number,
            'status' => $protectedInvoice->status_label,
            'is_paid' => $protectedInvoice->is_paid,
            'paid_at' => $protectedInvoice->paid_at ? $protectedInvoice->paid_at->toISOString() : null,
            'total_amount' => $protectedInvoice->formatted_total,
            'due_date' => $protectedInvoice->due_date->toISOString(),
            'is_overdue' => $protectedInvoice->isOverdue(),
            'download_count' => $protectedInvoice->download_count,
            'password_expires_at' => $protectedInvoice->password_expires_at ? $protectedInvoice->password_expires_at->toISOString() : null
        ]);
    }
    
    /**
     * Statistiques des factures pour l'API
     */
    public function getStats()
    {
        $userId = auth()->id();
        
        $stats = [
            'total_invoices' => ProtectedInvoicePdf::where('user_id', $userId)->count(),
            'paid_invoices' => ProtectedInvoicePdf::where('user_id', $userId)->where('is_paid', true)->count(),
            'pending_invoices' => ProtectedInvoicePdf::where('user_id', $userId)->where('is_paid', false)->count(),
            'overdue_invoices' => ProtectedInvoicePdf::where('user_id', $userId)
                ->where('is_paid', false)
                ->where('due_date', '<', now())
                ->count(),
            'total_revenue' => ProtectedInvoicePdf::where('user_id', $userId)
                ->where('is_paid', true)
                ->sum('total_amount'),
            'this_month_revenue' => ProtectedInvoicePdf::where('user_id', $userId)
                ->where('is_paid', true)
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount')
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Export des factures
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'in:csv,excel,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:paid,pending,overdue'
        ]);
        
        // TODO: Implémenter l'export selon le format demandé
        
        return response()->json(['message' => 'Export en cours de développement'], 501);
    }
    
    /**
     * Génère un mot de passe sécurisé
     */
    private function generateSecurePassword($length = 8)
    {
        // Combinaison de lettres majuscules, minuscules et chiffres
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
}