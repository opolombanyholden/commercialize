<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PricingPlanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Affiche la page de gestion des plans pour l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();
        $currentPlan = $user->pricingPlan ?? $this->getDefaultPlan();
        
        // Statistiques d'utilisation du mois en cours
        $monthlyStats = [
            'quotes_count' => $user->protectedPdfs()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'downloads_count' => $user->protectedPdfs()
                ->where('is_paid', true)
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->count(),
            'total_spent' => $user->protectedPdfs()
                ->where('is_paid', true)
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->count() * $currentPlan->pdf_download_price,
        ];
        
        return view('pricing.index', compact('user', 'currentPlan', 'monthlyStats'));
    }

    /**
     * Affiche tous les plans disponibles
     */
    public function showPlans()
    {
        $plans = PricingPlan::active()->ordered()->get();
        $currentPlan = Auth::user()->pricingPlan ?? $this->getDefaultPlan();
        
        return view('pricing.plans', compact('plans', 'currentPlan'));
    }

    /**
     * Change le plan de l'utilisateur
     */
    public function changePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:pricing_plans,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();
        $newPlan = PricingPlan::findOrFail($request->plan_id);
        
        // Vérifier que le plan est actif
        if (!$newPlan->is_active) {
            return back()->withErrors(['plan_id' => 'Ce plan n\'est plus disponible.']);
        }

        $oldPlan = $user->pricingPlan;
        
        // Mettre à jour le plan de l'utilisateur
        $user->update(['pricing_plan_id' => $newPlan->id]);
        
        Log::info('Changement de plan tarifaire', [
            'user_id' => $user->id,
            'old_plan' => $oldPlan ? $oldPlan->name : 'Aucun',
            'new_plan' => $newPlan->name,
            'ip' => request()->ip()
        ]);
        
        return redirect()->route('pricing.index')
            ->with('success', "Votre plan a été changé vers \"{$newPlan->name}\" avec succès !");
    }

    /**
     * Affiche la page de facturation et gestion du solde
     */
    public function billing()
    {
        $user = Auth::user();
        $currentPlan = $user->pricingPlan ?? $this->getDefaultPlan();
        
        // Historique des téléchargements payés
        $recentDownloads = $user->protectedPdfs()
            ->where('is_paid', true)
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get();
        
        return view('pricing.billing', compact('user', 'currentPlan', 'recentDownloads'));
    }

    /**
     * Ajoute des crédits au compte utilisateur
     */
    public function addCredits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:500|max:100000',
            'payment_method' => 'required|in:mobile_money,bank_transfer,cash',
            'payment_reference' => 'required|string|min:6|max:100',
        ], [
            'amount.required' => 'Le montant est requis.',
            'amount.numeric' => 'Le montant doit être numérique.',
            'amount.min' => 'Le montant minimum est de 500 FCFA.',
            'amount.max' => 'Le montant maximum est de 100 000 FCFA.',
            'payment_method.required' => 'La méthode de paiement est requise.',
            'payment_reference.required' => 'La référence de paiement est requise.',
            'payment_reference.min' => 'La référence doit contenir au moins 6 caractères.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        
        // Simuler la vérification du paiement
        $paymentVerified = $this->verifyPayment($request->payment_reference, $request->amount);
        
        if ($paymentVerified) {
            // Ajouter les crédits au compte
            $user->increment('account_balance', $request->amount);
            
            Log::info('Crédits ajoutés au compte', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'new_balance' => $user->fresh()->account_balance
            ]);
            
            return redirect()->route('pricing.billing')
                ->with('success', "Crédits ajoutés avec succès ! Nouveau solde : " . number_format($user->fresh()->account_balance, 0, ',', ' ') . " FCFA");
        } else {
            return back()->withErrors(['payment_reference' => 'Paiement non vérifié. Veuillez vérifier la référence.'])
                ->withInput();
        }
    }

    /**
     * Administration des plans (admin uniquement)
     */
    public function create()
    {
        $this->authorize('admin-access');
        return view('admin.pricing-plans.create');
    }

    /**
     * Stocke un nouveau plan (admin uniquement)
     */
    public function store(Request $request)
    {
        $this->authorize('admin-access');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans',
            'description' => 'nullable|string',
            'pdf_download_price' => 'required|numeric|min:0|max:10000',
            'max_quotes_per_month' => 'nullable|integer|min:1',
            'max_invoices_per_month' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        PricingPlan::create($request->all());
        
        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'Plan créé avec succès !');
    }

    /**
     * Affiche tous les plans (admin uniquement)
     */
    public function adminIndex()
    {
        $this->authorize('admin-access');
        
        $plans = PricingPlan::orderBy('sort_order')->get();
        return view('admin.pricing-plans.index', compact('plans'));
    }

    /**
     * Obtient le plan par défaut
     */
    private function getDefaultPlan()
    {
        return PricingPlan::where('slug', 'light')->first() ?? (object) [
            'name' => 'Light',
            'pdf_download_price' => 500,
            'formatted_price' => '500 FCFA',
            'max_quotes_per_month' => 10,
            'max_invoices_per_month' => 5,
        ];
    }

    /**
     * Simule la vérification de paiement
     */
    private function verifyPayment($paymentReference, $amount)
    {
        // Version Light : simulation basique
        // Dans les versions supérieures : intégration avec APIs de paiement
        
        // Pour la démo, on accepte si la référence contient au moins 6 caractères
        return strlen($paymentReference) >= 6;
    }
}