<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\ProtectedPdf;
use App\Models\PricingPlan;

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
     * Generate PDF with password and redirect to payment
     */
    public function generate(Request $request)
    {
        // Log de tentative de génération
        Log::info('Tentative de génération de devis', [
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        $user = Auth::user();
        
        // Validation des données du devis
        $validator = Validator::make($request->all(), [
            // Informations client
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'client_city' => 'nullable|string|max:100',
            
            // Informations devis
            'quote_number' => 'required|string|max:50',
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
            'client_name.required' => 'Le nom du client est obligatoire.',
            'quote_number.required' => 'Le numéro de devis est obligatoire.',
            'quote_date.required' => 'La date du devis est obligatoire.',
            'valid_until.required' => 'La date de validité est obligatoire.',
            'valid_until.after' => 'La date de validité doit être postérieure à la date du devis.',
            'quote_object.required' => 'L\'objet du devis est obligatoire.',
            'quote_type.required' => 'Le type de devis est obligatoire.',
            'quote_type.in' => 'Le type de devis doit être produit, service ou mixte.',
            'items.required' => 'Au moins un article est requis.',
            'items.min' => 'Au moins un article est requis.',
            'items.*.description.required' => 'La description de l\'article est obligatoire.',
            'items.*.type.required' => 'Le type d\'article est obligatoire.',
            'items.*.type.in' => 'Le type d\'article doit être produit ou service.',
            'items.*.quantity.required' => 'La quantité est obligatoire.',
            'items.*.quantity.min' => 'La quantité doit être supérieure à 0.',
            'items.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
            'items.*.unit_price.min' => 'Le prix unitaire doit être positif.',
            'applied_taxes.*.tax_id.exists' => 'Une taxe sélectionnée n\'existe pas.',
            'applied_taxes.*.apply_on.in' => 'Le champ "appliquer sur" doit être total, products ou services.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Vérifier l'unicité du numéro de devis
        $existingQuote = ProtectedPdf::where('quote_number', $request->quote_number)
            ->where('user_id', $user->id)
            ->first();
            
        if ($existingQuote) {
            return back()->withErrors(['quote_number' => 'Ce numéro de devis existe déjà.'])->withInput();
        }

        // Vérifier que toutes les taxes appartiennent à l'utilisateur
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
            // ÉTAPE 1 : GÉNÉRER LE MOT DE PASSE SÉCURISÉ
            $pdfPassword = $this->generateSecurePassword();
            
            // ÉTAPE 2 : PRÉPARER LES DONNÉES POUR LE PDF
            $quoteData = $this->prepareQuoteData($request->all(), $user);
            
            // Ajouter informations de sécurité
            $quoteData['security'] = [
                'generated_by' => $user->name,
                'ip_address' => request()->ip(),
                'generated_at' => now(),
                'hash' => hash('sha256', $quoteData['quote']['number'] . $user->id . now()->timestamp),
                'password' => $pdfPassword, // Inclure le mot de passe dans les données PDF
            ];
            
            // ÉTAPE 3 : GÉNÉRER LE PDF AVEC MOT DE PASSE VISIBLE
            $pdf = PDF::loadView('quotes.pdf', $quoteData);
            $pdf->setPaper('A4', 'portrait');
            
            // Obtenir le contenu PDF
            $pdfContent = $pdf->output();
            
            // Nom du fichier sécurisé
            $filename = 'Devis_' . $quoteData['quote']['number'] . '_' . now()->format('Ymd_His') . '.pdf';
            
            // ÉTAPE 4 : SAUVEGARDER LE PDF PROTÉGÉ EN BASE
            $protectedPdf = ProtectedPdf::create([
                'user_id' => $user->id,
                'quote_number' => $quoteData['quote']['number'],
                'filename' => $filename,
                'password' => $pdfPassword, // Le mot de passe pour déverrouiller
                'pdf_content' => base64_encode($pdfContent),
                'quote_data' => $quoteData,
                'client_email' => $quoteData['client']['email'],
                'client_phone' => $quoteData['client']['phone'],
                'total_amount' => $quoteData['totals']['total'],
                'security_hash' => $quoteData['security']['hash'],
                'is_paid' => false, // En attente de paiement
                'paid_at' => null,
                'payment_reference' => null,
            ]);
            
            // Log de succès
            Log::info('Devis généré avec succès', [
                'protected_pdf_id' => $protectedPdf->id,
                'quote_number' => $quoteData['quote']['number'],
                'user_id' => $user->id,
                'total_amount' => $quoteData['totals']['total'],
                'pdf_password' => $pdfPassword, // Pour debug (optionnel)
            ]);
            
            // ÉTAPE 5 : GÉNÉRER LE PDF D'INSTRUCTIONS (AVEC LIEN DE PAIEMENT)
            $instructionsPdf = $this->generateInstructionsPdf($quoteData, $protectedPdf->id);
            
            // ÉTAPE 6 : TÉLÉCHARGER LES INSTRUCTIONS ET REDIRIGER VERS PAIEMENT
            // Stocker le PDF d'instructions en session pour le télécharger après redirection
            session(['instructions_pdf' => [
                'content' => $instructionsPdf->output(),
                'filename' => 'Instructions_Devis_' . $quoteData['quote']['number'] . '.pdf'
            ]]);
            
            // Rediriger vers la page de paiement avec le PDF généré
            return redirect()->route('quotes.payments')
                ->with('success', 'Devis généré avec succès ! Téléchargez le PDF d\'instructions ci-dessous, puis procédez au paiement.')
                ->with('generated_quote_id', $protectedPdf->id)
                ->with('download_instructions', true);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['pdf' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Prepare quote data for PDF generation.
     */
    private function prepareQuoteData(array $requestData, $user)
    {
        // Récupérer les taxes qui seront appliquées
        $appliedTaxes = [];
        if (!empty($requestData['applied_taxes'])) {
            $taxIds = collect($requestData['applied_taxes'])->pluck('tax_id');
            $taxes = $user->taxes()->whereIn('id', $taxIds)->get()->keyBy('id');
            
            foreach ($requestData['applied_taxes'] as $appliedTax) {
                $tax = $taxes->get($appliedTax['tax_id']);
                if ($tax) {
                    $appliedTaxes[] = [
                        'tax' => $tax,
                        'apply_on' => $appliedTax['apply_on']
                    ];
                }
            }
        }
        
        // Préparer les articles
        $items = [];
        $subtotalProducts = 0;
        $subtotalServices = 0;
        
        foreach ($requestData['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            
            $items[] = [
                'description' => $item['description'],
                'type' => $item['type'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'line_total' => $lineTotal,
            ];
            
            // Additionner selon le type
            if ($item['type'] === 'produit') {
                $subtotalProducts += $lineTotal;
            } else {
                $subtotalServices += $lineTotal;
            }
        }
        
        $subtotal = $subtotalProducts + $subtotalServices;
        
        // Calculer les taxes appliquées
        $taxesDetails = [];
        $totalTax = 0;
        
        foreach ($appliedTaxes as $appliedTax) {
            $tax = $appliedTax['tax'];
            $applyOn = $appliedTax['apply_on'];
            
            // Déterminer la base de calcul
            $taxBase = 0;
            switch ($applyOn) {
                case 'total':
                    $taxBase = $subtotal;
                    break;
                case 'products':
                    $taxBase = $subtotalProducts;
                    break;
                case 'services':
                    $taxBase = $subtotalServices;
                    break;
            }
            
            $taxAmount = $tax->calculateTax($taxBase);
            $totalTax += $taxAmount;
            
            $taxesDetails[] = [
                'tax' => $tax,
                'apply_on' => $applyOn,
                'base' => $taxBase,
                'amount' => $taxAmount,
                'full_label' => $tax->full_label, // Libellé complet pour le PDF
                'is_discount' => $tax->is_discount,
                'type_label' => $tax->type_label,
            ];
        }
        
        return [
            'company' => [
                'name' => $user->company_name ?: $user->name,
                'address' => $user->full_address,
                'phone' => $user->phone,
                'email' => $user->email,
            ],
            'client' => [
                'name' => $requestData['client_name'],
                'email' => $requestData['client_email'] ?? null,
                'phone' => $requestData['client_phone'] ?? null,
                'address' => $requestData['client_address'] ?? null,
                'city' => $requestData['client_city'] ?? null,
            ],
            'quote' => [
                'number' => $requestData['quote_number'],
                'object' => $requestData['quote_object'],
                'type' => $requestData['quote_type'],
                'date' => Carbon::parse($requestData['quote_date']),
                'valid_until' => Carbon::parse($requestData['valid_until']),
                'notes' => $requestData['notes'] ?? null,
            ],
            'items' => $items,
            'totals' => [
                'subtotal_products' => $subtotalProducts,
                'subtotal_services' => $subtotalServices,
                'subtotal' => $subtotal,
                'taxes_details' => $taxesDetails,
                'total_tax' => $totalTax,
                'total' => $subtotal + $totalTax,
                'subtotal_words' => $this->amountToWords($subtotal),
                'total_words' => $this->amountToWords($subtotal + $totalTax),
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Génère un PDF d'instructions de paiement (AVEC lien de paiement)
     */
    private function generateInstructionsPdf($quoteData, $protectedPdfId)
    {
        $instructionsData = [
            'quote' => $quoteData['quote'],
            'client' => $quoteData['client'],
            'company' => $quoteData['company'],
            'totals' => $quoteData['totals'],
            'pdf_id' => $protectedPdfId,
            'access_url' => route('quotes.password-form', $protectedPdfId),
            'payment_url' => route('quotes.payments'), // NOUVEAU : Lien vers paiement
            'generated_at' => now(),
            'instructions' => [
                'step1' => 'Accédez au lien de paiement pour procéder au règlement',
                'step2' => 'Après paiement confirmé, vous recevrez le mot de passe par email/SMS',
                'step3' => 'Utilisez le lien d\'accès et le mot de passe pour télécharger le PDF final',
            ],
            'important_links' => [
                'payment' => route('quotes.payments'),
                'access' => route('quotes.password-form', $protectedPdfId),
            ]
        ];

        return PDF::loadView('quotes.instructions-pdf', $instructionsData);
    }

    /**
     * Affiche la vue de gestion des paiements avec le nouveau workflow
     */
    public function payments()
    {
        $user = Auth::user();
        
        // Récupérer les PDFs en attente de paiement
        $pendingPdfs = ProtectedPdf::where('user_id', $user->id)
            ->where('is_paid', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer le dernier devis généré si vient de generate()
        $lastGeneratedQuote = null;
        if (session('generated_quote_id')) {
            $lastGeneratedQuote = ProtectedPdf::find(session('generated_quote_id'));
        }

        // Récupérer le plan tarifaire de l'utilisateur (avec fallback)
        $pricingPlan = null;
        $downloadPrice = 500; // Prix par défaut
        
        try {
            $pricingPlan = $user->pricingPlan ?? PricingPlan::where('slug', 'light')->first();
            if ($pricingPlan) {
                $downloadPrice = $pricingPlan->pdf_download_price;
            }
        } catch (\Exception $e) {
            // Si la table pricing_plans n'existe pas encore
            $pricingPlan = (object) [
                'name' => 'Light',
                'pdf_download_price' => 500
            ];
        }
            
        return view('quotes.payment', compact(
            'pendingPdfs', 
            'lastGeneratedQuote', 
            'pricingPlan', 
            'downloadPrice'
        ));
    }

    /**
     * NOUVELLE MÉTHODE : Télécharger le PDF d'instructions depuis la session
     */
    public function downloadInstructions()
    {
        if (!session('instructions_pdf')) {
            return redirect()->route('quotes.payments')
                ->with('error', 'Aucun PDF d\'instructions disponible.');
        }
        
        $pdfData = session('instructions_pdf');
        
        // Supprimer de la session après téléchargement
        session()->forget('instructions_pdf');
        
        return response($pdfData['content'])
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $pdfData['filename'] . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|exists:protected_pdfs,id',
            'payment_method' => 'required|in:mobile_money,bank_transfer,cash',
            'payment_reference' => 'required|string|min:6|max:100',
            'notification_method' => 'required|in:email,sms',
            'client_contact' => 'required|string|max:255', // Email ou téléphone
        ], [
            'quote_id.required' => 'Le devis est requis.',
            'quote_id.exists' => 'Le devis sélectionné n\'existe pas.',
            'payment_method.required' => 'La méthode de paiement est requise.',
            'payment_reference.required' => 'La référence de paiement est requise.',
            'payment_reference.min' => 'La référence doit contenir au moins 6 caractères.',
            'notification_method.required' => 'La méthode de notification est requise.',
            'client_contact.required' => 'Le contact client est requis.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $protectedPdf = ProtectedPdf::where('id', $request->quote_id)
            ->where('user_id', Auth::id())
            ->where('is_paid', false)
            ->first();

        if (!$protectedPdf) {
            return back()->withErrors(['quote_id' => 'Devis non trouvé ou déjà traité.']);
        }

        // Récupérer le plan tarifaire pour le montant
        $user = Auth::user();
        try {
            $pricingPlan = $user->pricingPlan ?? PricingPlan::where('slug', 'light')->first();
            $downloadPrice = $pricingPlan->pdf_download_price ?? 500;
        } catch (\Exception $e) {
            $downloadPrice = 500;
        }

        // Simuler la vérification du paiement
        $paymentVerified = $this->verifyPayment($request->payment_reference, $downloadPrice);

        if ($paymentVerified) {
            // Marquer comme payé
            $protectedPdf->update([
                'is_paid' => true,
                'paid_at' => now(),
                'payment_reference' => $request->payment_reference,
            ]);
            
            // Mettre à jour le contact client si fourni
            if ($request->notification_method === 'email') {
                $protectedPdf->update(['client_email' => $request->client_contact]);
            } else {
                $protectedPdf->update(['client_phone' => $request->client_contact]);
            }
            
            Log::info('Paiement vérifié et devis débloqué', [
                'protected_pdf_id' => $protectedPdf->id,
                'quote_number' => $protectedPdf->quote_number,
                'payment_reference' => $request->payment_reference,
                'payment_method' => $request->payment_method,
                'amount_paid' => $downloadPrice,
                'user_id' => auth()->id()
            ]);
            
            // Envoyer le mot de passe selon la méthode choisie
            $this->sendPassword($protectedPdf, $request->notification_method);
            
            // Décompter du solde de l'utilisateur si applicable
            if ($user->account_balance >= $downloadPrice) {
                $user->decrement('account_balance', $downloadPrice);
            }
            
            return redirect()->route('quotes.payments')
                ->with('success', 'Paiement vérifié ! Le mot de passe a été envoyé au client.')
                ->with('password_sent', session('password_sent'));
        } else {
            Log::warning('Échec vérification paiement', [
                'quote_number' => $protectedPdf->quote_number,
                'payment_reference' => $request->payment_reference,
                'payment_method' => $request->payment_method,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            
            return back()->withErrors(['payment_reference' => 'Paiement non vérifié. Veuillez vérifier la référence.'])->withInput();
        }
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
        
        $protectedPdf = ProtectedPdf::findOrFail($id);
        
        // Log de tentative d'accès
        Log::info('Accès formulaire mot de passe', [
            'protected_pdf_id' => $id,
            'user_id' => auth()->id(),
            'client_email' => $protectedPdf->client_email,
            'ip' => request()->ip()
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
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $protectedPdf = ProtectedPdf::findOrFail($id);

        // Vérifier que le PDF a été payé
        if (!$protectedPdf->is_paid) {
            return back()->withErrors(['password' => 'Ce devis n\'a pas encore été payé.']);
        }

        if (!$protectedPdf->verifyPassword($request->password)) {
            Log::warning('Tentative de mot de passe incorrect', [
                'protected_pdf_id' => $id,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        // Log de téléchargement autorisé
        Log::info('Téléchargement PDF autorisé', [
            'protected_pdf_id' => $id,
            'quote_number' => $protectedPdf->quote_number,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        // Décoder et retourner le PDF avec headers sécurisés
        $pdfContent = base64_decode($protectedPdf->pdf_content);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $protectedPdf->filename . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // ... Conservez toutes vos autres méthodes existantes (numberToWords, amountToWords, etc.)

    /**
     * Convertit un nombre en mots français
     */
    private function numberToWords($number) {
        $number = (int) $number;
        
        if ($number == 0) {
            return 'zéro';
        }
        
        $units = [
            '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
            'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept',
            'dix-huit', 'dix-neuf'
        ];
        
        $tens = [
            '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante',
            'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'
        ];
        
        $scales = [
            '', 'mille', 'million', 'milliard', 'billion'
        ];
        
        $convertGroup = function($num) use ($units, $tens) {
            $result = '';
            
            // Centaines
            if ($num >= 100) {
                $hundreds = intval($num / 100);
                if ($hundreds == 1) {
                    $result .= 'cent';
                } else {
                    $result .= $units[$hundreds] . ' cent';
                }
                if ($num % 100 == 0 && $hundreds > 1) {
                    $result .= 's';
                }
                $num %= 100;
                if ($num > 0) {
                    $result .= ' ';
                }
            }
            
            // Dizaines et unités
            if ($num >= 20) {
                $tensDigit = intval($num / 10);
                $unitsDigit = $num % 10;
                
                if ($tensDigit == 7 || $tensDigit == 9) {
                    $result .= $tens[$tensDigit - 1];
                    if ($unitsDigit == 1 && $tensDigit == 7) {
                        $result .= ' et onze';
                    } elseif ($unitsDigit == 1 && $tensDigit == 9) {
                        $result .= ' et onze';
                    } else {
                        $result .= ' ' . $units[10 + $unitsDigit];
                    }
                } else {
                    $result .= $tens[$tensDigit];
                    if ($unitsDigit == 1 && $tensDigit > 1) {
                        $result .= ' et un';
                    } elseif ($unitsDigit > 0) {
                        $result .= ' ' . $units[$unitsDigit];
                    }
                    if ($tensDigit == 8 && $unitsDigit == 0) {
                        $result .= 's';
                    }
                }
            } elseif ($num > 0) {
                $result .= $units[$num];
            }
            
            return $result;
        };
        
        $result = '';
        $scaleIndex = 0;
        
        while ($number > 0) {
            $group = $number % 1000;
            
            if ($group > 0) {
                $groupWords = $convertGroup($group);
                
                if ($scaleIndex == 1 && $group == 1) {
                    // "mille" au lieu de "un mille"
                    $groupWords = '';
                }
                
                if ($scaleIndex > 0) {
                    if ($scaleIndex == 1) {
                        $groupWords .= ' ' . $scales[$scaleIndex];
                    } else {
                        $groupWords .= ' ' . $scales[$scaleIndex];
                        if ($group > 1) {
                            $groupWords .= 's';
                        }
                    }
                }
                
                if ($result == '') {
                    $result = $groupWords;
                } else {
                    $result = $groupWords . ' ' . $result;
                }
            }
            
            $number = intval($number / 1000);
            $scaleIndex++;
        }
        
        return trim($result);
    }

    /**
     * Convertit un montant en francs CFA en lettres
     */
    private function amountToWords($amount) {
        $amount = (float) $amount;
        $integerPart = (int) $amount;
        $decimalPart = round(($amount - $integerPart) * 100);
        
        $result = $this->numberToWords($integerPart);
        
        if ($integerPart <= 1) {
            $result .= ' franc CFA';
        } else {
            $result .= ' francs CFA';
        }
        
        if ($decimalPart > 0) {
            $result .= ' et ' . $this->numberToWords($decimalPart);
            if ($decimalPart <= 1) {
                $result .= ' centime';
            } else {
                $result .= ' centimes';
            }
        }
        
        return $result;
    }

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
     * Simule la vérification de paiement
     */
    private function verifyPayment($paymentProof, $expectedAmount) {
        // Version Light : simulation basique
        // Dans les versions supérieures : intégration avec APIs de paiement
        
        // Pour la démo, on accepte si la référence contient au moins 6 caractères
        return strlen($paymentProof) >= 6;
    }

    /**
     * Envoie le mot de passe au client
     */
    private function sendPassword($protectedPdf, $method) {
        $password = $protectedPdf->password;
        $quoteNumber = $protectedPdf->quote_number;
        
        if ($method === 'email' && $protectedPdf->client_email) {
            $this->sendPasswordByEmail($protectedPdf->client_email, $password, $quoteNumber);
        } elseif ($method === 'sms' && $protectedPdf->client_phone) {
            $this->sendPasswordBySms($protectedPdf->client_phone, $password, $quoteNumber);
        }
    }

    /**
     * Envoie le mot de passe par email
     */
    private function sendPasswordByEmail($email, $password, $quoteNumber) {
        // Version Light : log du message (pas d'envoi réel)
        // Dans les versions supérieures : envoi via Mailtrap, SendGrid, etc.
        
        $message = "
        Bonjour,
        
        Votre paiement pour le téléchargement du devis {$quoteNumber} a été confirmé.
        
        Mot de passe pour télécharger le PDF : {$password}
        
        Accédez à votre devis via le lien qui vous a été communiqué.
        
        Merci de votre confiance.
        
        CommercialiZe
        ";
        
        // Log pour la version Light
        Log::info("Email envoyé à {$email} : " . $message);
        
        // Ajouter un message pour l'utilisateur
        session()->flash('password_sent', "Mot de passe envoyé par email à {$email} : {$password}");
    }

    /**
     * Envoie le mot de passe par SMS
     */
    private function sendPasswordBySms($phone, $password, $quoteNumber) {
        // Version Light : log du message (pas d'envoi réel)
        // Dans les versions supérieures : intégration SMS via Twilio, etc.
        
        $message = "CommercialiZe: Paiement confirmé pour téléchargement devis {$quoteNumber}. Mot de passe PDF: {$password}";
        
        // Log pour la version Light
        Log::info("SMS envoyé à {$phone} : " . $message);
        
        // Ajouter un message pour l'utilisateur
        session()->flash('password_sent', "Mot de passe envoyé par SMS à {$phone} : {$password}");
    }
}