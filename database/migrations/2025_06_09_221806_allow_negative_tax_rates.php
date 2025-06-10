<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note: Laravel/MySQL ne permet pas de modifier facilement les contraintes CHECK
        // Nous allons gérer les taux négatifs au niveau de l'application
        // La colonne rate reste decimal(5,2) mais on acceptera les valeurs négatives
        
        // Aucune modification de structure nécessaire
        // La validation sera gérée dans les contrôleurs et modèles
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rien à faire
    }
};