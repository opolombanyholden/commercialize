<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $taxes = $user->taxes()->orderBy('is_active', 'desc')->orderBy('name')->get();
        
        $stats = [
            'total' => $taxes->count(),
            'active' => $taxes->where('is_active', true)->count(),
            'inactive' => $taxes->where('is_active', false)->count(),
            'taxes' => $taxes->where('rate', '>=', 0)->count(),
            'discounts' => $taxes->where('rate', '<', 0)->count(),
        ];

        return view('taxes.index', compact('taxes', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('taxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:-100|max:1000', // Étendu pour supporter plus de flexibilité
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Le nom de la taxe/remise est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'rate.required' => 'Le taux est obligatoire.',
            'rate.numeric' => 'Le taux doit être un nombre.',
            'rate.min' => 'Le taux ne peut pas être inférieur à -100% (remise maximale).',
            'rate.max' => 'Le taux ne peut pas dépasser 1000%.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Vérifier si une taxe avec le même nom existe déjà pour cet utilisateur
        $existingTax = Auth::user()->taxes()
            ->where('name', $request->name)
            ->first();

        if ($existingTax) {
            return back()->withErrors(['name' => 'Une taxe/remise avec ce nom existe déjà.'])->withInput();
        }

        // Créer la nouvelle taxe
        $tax = Auth::user()->taxes()->create([
            'name' => $request->name,
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        $type = $tax->isDiscount ? 'Remise' : 'Taxe';
        
        return redirect()->route('taxes.index')->with('success', "{$type} \"{$tax->name}\" créée avec succès !");
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax)
    {
        // Vérifier que la taxe appartient à l'utilisateur connecté
        if ($tax->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('taxes.show', compact('tax'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tax $tax)
    {
        // Vérifier que la taxe appartient à l'utilisateur connecté
        if ($tax->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('taxes.edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax)
    {
        // Vérifier que la taxe appartient à l'utilisateur connecté
        if ($tax->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:-100|max:1000',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Le nom de la taxe/remise est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'rate.required' => 'Le taux est obligatoire.',
            'rate.numeric' => 'Le taux doit être un nombre.',
            'rate.min' => 'Le taux ne peut pas être inférieur à -100% (remise maximale).',
            'rate.max' => 'Le taux ne peut pas dépasser 1000%.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Vérifier si une autre taxe avec le même nom existe pour cet utilisateur
        $existingTax = Auth::user()->taxes()
            ->where('name', $request->name)
            ->where('id', '!=', $tax->id)
            ->first();

        if ($existingTax) {
            return back()->withErrors(['name' => 'Une autre taxe/remise avec ce nom existe déjà.'])->withInput();
        }

        $oldType = $tax->type_label;

        // Mettre à jour la taxe
        $tax->update([
            'name' => $request->name,
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        $newType = $tax->fresh()->type_label;
        
        return redirect()->route('taxes.index')->with('success', "{$newType} \"{$tax->name}\" modifiée avec succès !");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        // Vérifier que la taxe appartient à l'utilisateur connecté
        if ($tax->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        $taxName = $tax->name;
        $type = $tax->type_label;
        $tax->delete();

        return redirect()->route('taxes.index')->with('success', "La {$type} \"{$taxName}\" a été supprimée avec succès !");
    }

    /**
     * Toggle the active status of a tax.
     */
    public function toggleStatus(Tax $tax)
    {
        // Vérifier que la taxe appartient à l'utilisateur connecté
        if ($tax->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        $tax->update([
            'is_active' => !$tax->is_active
        ]);

        $status = $tax->is_active ? 'activée' : 'désactivée';
        $type = strtolower($tax->type_label);
        
        return redirect()->route('taxes.index')->with('success', "La {$type} \"{$tax->name}\" a été {$status} avec succès !");
    }

// Dans app/Http/Controllers/TaxController.php - modifiez les règles de validation :

    private function getValidationRules()
    {
        return [
            'name' => 'required|string|max:100',
            'type' => 'required|in:percentage,fixed',
            'rate' => 'required|numeric|min:-100|max:1000', // Permet les taux négatifs
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
    
    private function getValidationMessages()
    {
        return [
            'name.required' => 'Le nom de la taxe est obligatoire.',
            'type.required' => 'Le type de taxe est obligatoire.',
            'type.in' => 'Le type doit être pourcentage ou montant fixe.',
            'rate.required' => 'Le taux est obligatoire.',
            'rate.numeric' => 'Le taux doit être un nombre.',
            'rate.min' => 'Le taux doit être supérieur ou égal à -100.',
            'rate.max' => 'Le taux ne peut pas dépasser 1000.',
        ];
    }

    /**
     * Get taxes for AJAX requests (for quote/invoice forms)
     */
    public function getTaxesForSelect()
    {
        $user = Auth::user();
        $taxes = $user->activeTaxes()->orderBy('name')->get();
        
        return response()->json([
            'taxes' => $taxes->map(function ($tax) {
                return [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                    'display_name' => $tax->display_name,
                    'full_label' => $tax->full_label,
                    'is_discount' => $tax->is_discount,
                    'type_label' => $tax->type_label,
                    'styled_rate' => $tax->styled_rate,
                ];
            })
        ]);
    }
}