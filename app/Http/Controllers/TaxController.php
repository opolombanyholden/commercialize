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
            'rate' => 'required|numeric|min:-100|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Le nom de la taxe est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'rate.required' => 'Le taux de la taxe est obligatoire.',
            'rate.numeric' => 'Le taux doit être un nombre.',
            'rate.min' => 'Le taux ne peut pas être inférieur à -100%.',
            'rate.max' => 'Le taux ne peut pas dépasser 100%.',
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
            return back()->withErrors(['name' => 'Une taxe avec ce nom existe déjà.'])->withInput();
        }

        // Créer la nouvelle taxe
        Auth::user()->taxes()->create([
            'name' => $request->name,
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('taxes.index')->with('success', 'Taxe créée avec succès !');
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
            'rate' => 'required|numeric|min:-100|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Le nom de la taxe est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'rate.required' => 'Le taux de la taxe est obligatoire.',
            'rate.numeric' => 'Le taux doit être un nombre.',
            'rate.min' => 'Le taux ne peut pas être inférieur à -100%.',
            'rate.max' => 'Le taux ne peut pas dépasser 100%.',
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
            return back()->withErrors(['name' => 'Une autre taxe avec ce nom existe déjà.'])->withInput();
        }

        // Mettre à jour la taxe
        $tax->update([
            'name' => $request->name,
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('taxes.index')->with('success', 'Taxe modifiée avec succès !');
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
        $tax->delete();

        return redirect()->route('taxes.index')->with('success', "La taxe \"{$taxName}\" a été supprimée avec succès !");
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
        
        return redirect()->route('taxes.index')->with('success', "La taxe \"{$tax->name}\" a été {$status} avec succès !");
    }
}