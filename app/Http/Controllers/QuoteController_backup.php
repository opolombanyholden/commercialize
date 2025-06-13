<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Requests\QuotePasswordRequest;
use App\Services\PDFSecurityService;
// Assurez-vous d'importer votre modèle ProtectedPdf
use App\Models\ProtectedPdf;

class QuoteController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new quote.
     */
    public function create()
    {
        // Vérification des autorisations avec Policy
        /*if (!auth()->user()->can('create', 'quote')) {
            abort(403, 'Action non autorisée');
        }/**/
        
        $user = Auth::user();
        $taxes = $user->activeTaxes()->orderBy('name')->get();
        
        // Vérifier qu'il y a au moins une taxe active
        if ($taxes->isEmpty()) {
            return redirect()->route('taxes.create')
                ->with('error', 'Vous devez créer au moins une taxe active avant de pouvoir générer un devis.');
        }

        return view('quotes.create', compact('taxes'));
    }

    /**
     * Generate and download the quote PDF.
     */
    public function generate(Request $request)
    {
        // Log de tentative de génération
        Log::info('Tentative de génération de devis', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $user = Auth::user();
        
        // Validation des données du devis (votre validation existante)
        $validator = Validator::make($request->all(), [
            // Informations client
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'client_city' => 'nullable|string|max:100',
            
            // Informations devis
            'quote_number' => 'required|string|max:50|unique:protected_pdfs,quote_number', // Ajout unique
            'quote_date' => 'required|date',
            'valid_until' => 'required|date|after:quote_date',
            'quote_object' => 'required|string|max:255',
            'quote_type' => 'required|in:produit,service,mixte',
            'notes' => 'nullable|string|max:1000',
            
            // Lignes du devis
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.type' => 'required|in:produit,service',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            
            // Taxes appliquées
            'applied_taxes' => 'nullable|array',
            'applied_taxes.*.tax_id' => 'required_with:applied_taxes|exists:taxes,id',
            'applied_taxes.*.apply_on' => 'required_with:applied_taxes|in:total,products,services',
        ], [
            // Vos messages existants + nouveau message
            'quote_number.unique' => 'Ce numéro de devis existe déjà.',
            // ... autres messages existants
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Votre validation métier existante...
        if (!empty($request->applied_taxes)) {
            $taxIds = collect($request->applied_taxes)->pluck('tax_id')->unique();
            $userTaxIds = $user->taxes()->pluck('id');
            
            if (!$taxIds->every(function ($taxId) use ($userTaxIds) {
                return $userTaxIds->contains($taxId);
            })) {
                return back()->withErrors(['applied_taxes' => 'Une ou plusieurs taxes sélectionnées ne vous appartiennent pas.'])->withInput();
            }
        }

        // Validation métier : vérifier la cohérence type devis / type articles
        $quoteType = $request->quote_type;
        $itemTypes = collect($request->items)->pluck('type')->unique();
        
        if ($quoteType === 'produit' && $itemTypes->contains('service')) {
            return back()->withErrors(['items' => 'Un devis de type "Produits uniquement" ne peut contenir d\'articles de type "Service".'])->withInput();
        }
        
        if ($quoteType === 'service' && $itemTypes->contains('produit')) {
            return back()->withErrors(['items' => 'Un devis de type "Services uniquement" ne peut contenir d\'articles de type "Produit".'])->withInput();
        }

        try {
            // Générer un mot de passe sécurisé
            $pdfPassword = $this->generateSecurePassword();
            
            // Préparer les données pour le PDF
            $quoteData = $this->prepareQuoteData($request->all(), $user);
            
            // Ajouter hash de sécurité aux données
            $securityService = new PDFSecurityService();
            $quoteData['security'] = [
                'hash' => $securityService->generateSecurityHash(
                    time(), // Utilise timestamp comme ID temporaire
                    $user->id
                ),
                'generated_by' => $user->name,
                'ip_address' => request()->ip()
            ];
            
            // Générer le PDF (sans protection native)
            $pdf = PDF::loadView('quotes.pdf', $quoteData);
            $pdf->setPaper('A4', 'portrait');
            
            // Obtenir le contenu PDF
            $pdfContent = $pdf->output();
            
            // Nom du fichier sécurisé
            $filename = 'Devis_' . $quoteData['quote']['number'] . '_' . now()->format('Ymd_His') . '.pdf';
            
            // Stocker le PDF protégé en base de données
            $protectedPdf = ProtectedPdf::create([
                'user_id' => $user->id,
                'quote_number' => $quoteData['quote']['number'],
                'filename' => $filename,
                'password' => $pdfPassword,
                'pdf_content' => base64_encode($pdfContent),
                'quote_data' => $quoteData,
                'client_email' => $quoteData['client']['email'],
                'client_phone' => $quoteData['client']['phone'],
                'total_amount' => $quoteData['totals']['total'],
                'security_hash' => $quoteData['security']['hash'], // Nouveau champ
            ]);
            
            // Log de succès
            Log::info('Devis généré avec succès', [
                'protected_pdf_id' => $protectedPdf->id,
                'quote_number' => $quoteData['quote']['number'],
                'user_id' => $user->id,
                'total_amount' => $quoteData['totals']['total']
            ]);
            
            // Générer un PDF temporaire avec instructions de paiement
            $instructionsPdf = $this->generateInstructionsPdf($quoteData, $pdfPassword, $protectedPdf->id);
            
            return $instructionsPdf->download('Instructions_' . $quoteData['quote']['number'] . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['pdf' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()])->withInput();
        }
    }

    // ... Vos méthodes existantes prepareQuoteData, numberToWords, amountToWords restent identiques ...

    /**
     * Génère un mot de passe sécurisé pour le PDF (version améliorée)
     */
    private function generateSecurePassword($length = 8) {
        // Caractères plus sécurisés (évite confusion 0/O, 1/l)
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Affiche la vue de gestion des paiements
     */
    public function payments()
    {
        $user = Auth::user();
        $pendingPdfs = ProtectedPdf::where('user_id', $user->id)
            ->where('is_paid', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('quotes.payment', compact('pendingPdfs'));
    }

    /**
     * Affiche le formulaire de saisie du mot de passe (version sécurisée)
     */
    public function showPasswordForm($id)
    {
        // Validation de l'ID
        if (!is_numeric($id) || $id <= 0) {
            abort(404, 'Devis non trouvé');
        }
        
        // Vérification des autorisations
        /*$this->authorize('access', ['quote', $id]);*/
        
        $protectedPdf = ProtectedPdf::findOrFail($id);
        
        // Log de tentative d'accès
        Log::info('Accès formulaire mot de passe', [
            'protected_pdf_id' => $id,
            'user_id' => auth()->id(),
            'client_email' => $protectedPdf->client_email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        return view('quotes.password-form', compact('protectedPdf'));
    }

    /**
     * Vérifie le mot de passe et télécharge le PDF (version sécurisée)
     */
    public function downloadWithPassword(Request $request, $id)
    {
        // Validation de l'ID
        if (!is_numeric($id) || $id <= 0) {
            abort(404);
        }
        
        // Vérification des autorisations
        /*$this->authorize('download', ['quote', $id]);*/
        
        // Validation du mot de passe
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:' . config('pdf.password_min_length', 8),
                'max:255'
            ]
        ], [
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au minimum :min caractères.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Rate limiting spécifique aux tentatives de mot de passe
        $key = 'password-attempts:' . auth()->id() . ':' . $id;
        
        if (RateLimiter::tooManyAttempts($key, config('pdf.max_attempts_per_minute', 3))) {
            Log::warning('Trop de tentatives de mot de passe', [
                'protected_pdf_id' => $id,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            
            return back()->withErrors([
                'password' => 'Trop de tentatives. Réessayez dans 5 minutes.'
            ]);
        }

        $protectedPdf = ProtectedPdf::findOrFail($id);

        if (!$protectedPdf->verifyPassword($request->password)) {
            // Incrémenter le compteur de tentatives
            RateLimiter::hit($key, config('pdf.rate_limit_decay', 300));
            
            Log::warning('Tentative de mot de passe incorrect', [
                'protected_pdf_id' => $id,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'attempts' => RateLimiter::attempts($key)
            ]);
            
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        // Succès - Clear rate limit
        RateLimiter::clear($key);
        
        // Log de téléchargement autorisé
        Log::info('Téléchargement PDF autorisé', [
            'protected_pdf_id' => $id,
            'quote_number' => $protectedPdf->quote_number,
            'user_id' => auth()->id(),
            'client_email' => $protectedPdf->client_email,
            'ip' => request()->ip()
        ]);

        // Décoder et retourner le PDF avec headers sécurisés
        $pdfContent = base64_decode($protectedPdf->pdf_content);
        
        $securityService = new PDFSecurityService();
        $headers = $securityService->getSecurityHeaders();
        $headers['Content-Disposition'] = 'attachment; filename="' . $protectedPdf->filename . '"';
        
        return response($pdfContent, 200, $headers);
    }

    // ... Vos autres méthodes existantes (generateInstructionsPdf, getPaymentInstructions, etc.) restent identiques ...

    /**
     * Méthode pour débloquer le PDF après paiement (version sécurisée)
     */
    public function unlockPdf(Request $request) {
        $validator = Validator::make($request->all(), [
            'quote_number' => 'required|string|max:50',
            'payment_proof' => 'required|string|min:6|max:100',
            'notification_method' => 'required|in:email,sms',
        ], [
            'payment_proof.min' => 'La référence de paiement doit contenir au minimum 6 caractères.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $protectedPdf = ProtectedPdf::where('quote_number', $request->quote_number)
            ->where('user_id', Auth::id())
            ->where('is_paid', false)
            ->first();

        if (!$protectedPdf) {
            Log::warning('Tentative de déblocage sur devis inexistant', [
                'quote_number' => $request->quote_number,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            
            return back()->withErrors(['quote_number' => 'Devis non trouvé ou déjà traité.']);
        }

        // Simuler la vérification du paiement
        $paymentVerified = $this->verifyPayment($request->payment_proof, $protectedPdf->total_amount);

        if ($paymentVerified) {
            // Marquer comme payé
            $protectedPdf->markAsPaid();
            
            Log::info('Paiement vérifié et devis débloqué', [
                'protected_pdf_id' => $protectedPdf->id,
                'quote_number' => $request->quote_number,
                'payment_proof' => $request->payment_proof,
                'user_id' => auth()->id()
            ]);
            
            // Envoyer le mot de passe par email ou SMS
            $this->sendPassword($protectedPdf, $request->notification_method);
            
            return back()->with('success', 'Paiement vérifié ! Le mot de passe a été envoyé au client.');
        } else {
            Log::warning('Échec vérification paiement', [
                'quote_number' => $request->quote_number,
                'payment_proof' => $request->payment_proof,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            
            return back()->withErrors(['payment_proof' => 'Paiement non vérifié. Veuillez vérifier la référence.']);
        }
    }

    // ... Vos autres méthodes existantes restent identiques ...
}