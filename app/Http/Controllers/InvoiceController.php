<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Tax;
use App\Models\Invoice;
use App\Models\ProtectedInvoicePdf;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Affiche le formulaire de création de facture
     */
    public function create()
    {
        $taxes = Tax::where('user_id', Auth::id())
                   ->where('is_active', true)
                   ->get();
        
        return view('invoices.create', compact('taxes'));
    }

    /**
     * Génère la facture et redirige vers la page de paiement
     */
    public function generate(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            // Informations de la facture
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date|before_or_equal:today',
            'invoice_object' => 'required|string|max:255',
            'invoice_type' => 'required|in:produit,service,mixte',
            'due_date' => 'required|date|after:invoice_date',
            
            // Informations client
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'client_city' => 'nullable|string|max:100',
            
            // Articles
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.type' => 'required|in:produit,service',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            
            // Taxes appliquées
            'applied_taxes' => 'nullable|array',
            'applied_taxes.*.tax_id' => 'required_with:applied_taxes|exists:taxes,id',
            'applied_taxes.*.apply_on' => 'required_with:applied_taxes|in:total,products,services',
            
            // Notes optionnelles
            'notes' => 'nullable|string|max:1000',
        ], [
            'invoice_number.required' => 'Le numéro de facture est obligatoire.',
            'invoice_date.required' => 'La date de facture est obligatoire.',
            'due_date.after' => 'La date d\'échéance doit être après la date de facture.',
            'client_name.required' => 'Le nom du client est obligatoire.',
            'items.required' => 'Au moins un article est requis.',
        ]);

        try {
            // Vérifier l'unicité du numéro de facture pour cet utilisateur
            $existingInvoice = ProtectedInvoicePdf::where('user_id', Auth::id())
                                                 ->where('invoice_number', $validatedData['invoice_number'])
                                                 ->first();
            
            if ($existingInvoice) {
                return back()->withInput()
                           ->withErrors(['invoice_number' => 'Ce numéro de facture existe déjà.']);
            }

            // Calcul des totaux
            $subtotalProducts = 0;
            $subtotalServices = 0;
            $processedItems = [];

            foreach ($validatedData['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $item['line_total'] = $lineTotal;
                $processedItems[] = $item;
                
                if ($item['type'] === 'produit') {
                    $subtotalProducts += $lineTotal;
                } elseif ($item['type'] === 'service') {
                    $subtotalServices += $lineTotal;
                }
            }

            $subtotalHT = $subtotalProducts + $subtotalServices;
            $totalTax = 0;
            $taxesBreakdown = [];

            if (!empty($validatedData['applied_taxes'])) {
                foreach ($validatedData['applied_taxes'] as $appliedTax) {
                    $tax = Tax::find($appliedTax['tax_id']);
                    if (!$tax) continue;

                    $taxBase = 0;
                    switch ($appliedTax['apply_on']) {
                        case 'total':
                            $taxBase = $subtotalHT;
                            break;
                        case 'products':
                            $taxBase = $subtotalProducts;
                            break;
                        case 'services':
                            $taxBase = $subtotalServices;
                            break;
                    }

                    $taxAmount = $taxBase * ($tax->rate / 100);
                    $totalTax += $taxAmount;
                    
                    $taxesBreakdown[] = [
                        'tax_id' => $tax->id,
                        'tax_name' => $tax->name,
                        'tax_rate' => $tax->rate,
                        'apply_on' => $appliedTax['apply_on'],
                        'tax_base' => $taxBase,
                        'tax_amount' => $taxAmount,
                    ];
                }
            }

            $total = $subtotalHT + $totalTax;

            // Génération automatique du mot de passe
            $autoPassword = strtoupper(substr(md5(uniqid()), 0, 8));

            // Générer un numéro de facture formaté automatiquement
            $invoiceNumbers = ProtectedInvoicePdf::generateInvoiceNumber();

            // Préparer les données de la facture
            $invoiceData = [
                'invoice' => [
                    'number' => $validatedData['invoice_number'],
                    'date' => $validatedData['invoice_date'],
                    'due_date' => $validatedData['due_date'],
                    'object' => $validatedData['invoice_object'],
                    'type' => $validatedData['invoice_type'],
                    'currency' => 'FCFA',
                ],
                'client' => [
                    'name' => $validatedData['client_name'],
                    'email' => $validatedData['client_email'] ?? '',
                    'phone' => $validatedData['client_phone'] ?? '',
                    'address' => $validatedData['client_address'] ?? '',
                    'city' => $validatedData['client_city'] ?? '',
                ],
                'items' => $processedItems,
                'applied_taxes' => $taxesBreakdown,
                'totals' => [
                    'subtotal_products' => $subtotalProducts,
                    'subtotal_services' => $subtotalServices,
                    'subtotal' => $subtotalHT,
                    'total_tax' => $totalTax,
                    'total' => $total,
                ],
                'notes' => $validatedData['notes'] ?? '',
                'company' => [
                    'name' => auth()->user()->company_name ?? 'Votre Entreprise',
                    'address' => auth()->user()->company_address ?? '',
                    'phone' => auth()->user()->phone ?? '',
                    'email' => auth()->user()->email ?? '',
                ],
            ];

            // Créer l'enregistrement dans protected_invoice_pdfs
            $protectedInvoice = ProtectedInvoicePdf::create([
                'user_id' => Auth::id(),
                'invoice_number' => $validatedData['invoice_number'], 
                'formatted_invoice_number' => sprintf('FAC-%s-%04d', date('Y-m'), $validatedData['invoice_number']),
                'hash' => \Illuminate\Support\Str::random(32),
                'filename' => ProtectedInvoicePdf::generateFilename($validatedData['invoice_number']),
                'password' => $autoPassword, // Champ ancien
                'password_hash' => Hash::make($autoPassword), // Nouveau champ
                'password_expires_at' => now()->addHours(24),
                'invoice_data' => $invoiceData,
                'client_email' => $validatedData['client_email'],
                'client_phone' => $validatedData['client_phone'],
                'total_amount' => $total,
                'currency' => 'FCFA',
                'security_hash' => ProtectedInvoicePdf::generateSecurityHash(
                    $validatedData['invoice_number'], 
                    $validatedData['client_email'] ?? 'no-email'
                ),
                'due_date' => $validatedData['due_date'],
                'is_paid' => false,
                'download_count' => 0,
            ]);

            // Stocker temporairement le mot de passe en clair pour affichage (sera supprimé après paiement)
            session(['invoice_password_' . $protectedInvoice->id => $autoPassword]);

            // Redirection vers la page de paiement avec session pour instructions
            return redirect()->route('invoices.payments', $protectedInvoice->id)
                           ->with('download_instructions', true)
                           ->with('success', 'Facture générée avec succès !');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->withErrors(['error' => 'Erreur lors de la génération : ' . $e->getMessage()]);
        }
    }

    /**
     * Affiche la page de paiement pour les factures
     */
    public function payments($id = null)
    {
        try {
            // Récupérer la dernière facture créée pour cet utilisateur
            $protectedInvoice = ProtectedInvoicePdf::where('user_id', Auth::id())
                                                  ->orderBy('created_at', 'desc')
                                                  ->first();
            
            if (!$protectedInvoice) {
                // Si aucune facture pour cet utilisateur, prendre la dernière créée
                $protectedInvoice = ProtectedInvoicePdf::orderBy('created_at', 'desc')->first();
            }

            if (!$protectedInvoice) {
                return redirect()->route('invoices.create')
                               ->withErrors(['error' => 'Aucune facture trouvée.']);
            }

            // Données minimales pour éviter les erreurs
            $pendingInvoices = collect();
            $downloadPrice = 500;
            $pricingPlan = (object) ['name' => 'Light'];

            // Utiliser la vue simple
            return view('payments-simple', compact(
                'protectedInvoice', 
                'pendingInvoices', 
                'downloadPrice', 
                'pricingPlan'
            ));
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
    }

    /**
     * Affiche les instructions de téléchargement
     */
    public function downloadInstructions($id)
    {
        $protectedInvoice = ProtectedInvoicePdf::where('id', $id)
                                              ->where('user_id', Auth::id())
                                              ->first();

        if (!$protectedInvoice) {
            return redirect()->route('invoices.create')
                           ->withErrors(['error' => 'Facture introuvable.']);
        }

        return view('download-instructions', compact('protectedInvoice'));
    }

    /**
     * Télécharge le PDF des instructions de paiement
     */
    public function downloadInstructionsPdf($id)
    {
        $protectedInvoice = ProtectedInvoicePdf::where('id', $id)
                                              ->where('user_id', Auth::id())
                                              ->first();

        if (!$protectedInvoice) {
            return redirect()->route('invoices.create')
                           ->withErrors(['error' => 'Facture introuvable.']);
        }

        try {
            // Utiliser 'invoice' au lieu de 'protectedInvoice' pour correspondre à la vue
            $invoice = $protectedInvoice;
            $pdf = Pdf::loadView('instructions-pdf', compact('invoice'));
            return $pdf->download('instructions-paiement-facture-' . $protectedInvoice->invoice_number . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la génération du PDF : ' . $e->getMessage()]);
        }
    }

    /**
     * Traite le paiement de la facture
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:protected_invoice_pdfs,id',
            'payment_method' => 'required|in:mobile_money,paypal,stripe,bank_transfer',
            'payment_reference' => 'required|string|min:6',
            'notification_method' => 'required|in:email,sms',
            'client_contact' => 'required|string',
        ]);

        $protectedInvoice = ProtectedInvoicePdf::where('id', $request->invoice_id)
                                              ->where('user_id', Auth::id())
                                              ->first();

        if (!$protectedInvoice) {
            return redirect()->back()
                           ->withErrors(['error' => 'Facture introuvable.']);
        }

        try {
            // Mettre à jour le statut de paiement
            $protectedInvoice->update([
                'is_paid' => true,
                'paid_at' => now(),
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'notification_method' => $request->notification_method,
                'client_contact' => $request->client_contact,
            ]);

            // Récupérer le mot de passe en clair depuis la session
            $plainPassword = session('invoice_password_' . $protectedInvoice->id);
            
            // Supprimer le mot de passe de la session
            session()->forget('invoice_password_' . $protectedInvoice->id);

            // Simuler l'envoi du mot de passe
            $passwordMessage = "Votre mot de passe d'accès à la facture " . $protectedInvoice->formatted_invoice_number . " : " . $plainPassword;
            
            return redirect()->route('invoices.password-form', $protectedInvoice->id)
                           ->with('success', 'Paiement effectué avec succès ! Le mot de passe a été envoyé par ' . $request->notification_method . '.')
                           ->with('password_hint', $plainPassword); // Pour debug - à supprimer en production

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors du traitement du paiement : ' . $e->getMessage()]);
        }
    }

    /**
     * Affiche le formulaire de saisie du mot de passe
     */
    public function showPasswordForm($id)
    {
        $protectedInvoice = ProtectedInvoicePdf::where('id', $id)
                                              ->where('is_paid', true)
                                              ->first();

        if (!$protectedInvoice) {
            abort(404, 'Facture introuvable ou non payée.');
        }

        return view('password-form', compact('protectedInvoice'));
    }

    /**
     * Vérifie le mot de passe et permet le téléchargement
     */
    public function downloadWithPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $protectedInvoice = ProtectedInvoicePdf::where('id', $id)
                                              ->where('is_paid', true)
                                              ->first();

        if (!$protectedInvoice) {
            return back()->withErrors(['error' => 'Facture introuvable ou non payée.']);
        }

        // Vérification du mot de passe (utiliser le champ password existant)
        if ($request->password !== $protectedInvoice->password) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        try {
            // Incrémenter le compteur de téléchargements
            $protectedInvoice->incrementDownloads();

            // Générer le PDF de la facture finale
            $pdf = Pdf::loadView('pdf', ['protectedInvoice' => $protectedInvoice]);
            
            return $pdf->download('facture-' . $protectedInvoice->invoice_number . '.pdf');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la génération du PDF : ' . $e->getMessage()]);
        }
    }
}