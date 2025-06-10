<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Statistiques pour le dashboard Light
        $stats = [
            'version' => $user->version_name,
            'taxes_count' => $user->activeTaxes()->count(),
            'company_name' => $user->company_name ?: 'Non défini',
            'user_since' => $user->created_at->format('d/m/Y'),
        ];

        return view('dashboard', compact('stats', 'user'));
    }
}