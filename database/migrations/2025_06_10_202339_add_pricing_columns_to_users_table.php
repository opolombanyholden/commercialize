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
        Schema::table('users', function (Blueprint $table) {
            // Vérifier et ajouter pricing_plan_id seulement si elle n'existe pas
            if (!Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')
                      ->nullable()
                      ->after('email')
                      ->constrained('pricing_plans')
                      ->onDelete('set null');
            }
            
            // Vérifier et ajouter account_balance seulement si elle n'existe pas
            if (!Schema::hasColumn('users', 'account_balance')) {
                $table->decimal('account_balance', 10, 2)
                      ->default(0)
                      ->after('pricing_plan_id')
                      ->comment('Solde du compte en FCFA');
            }
        });
        
        // Ajouter les index seulement s'ils n'existent pas déjà
        Schema::table('users', function (Blueprint $table) {
            $indexes = Schema::getConnection()->getDoctrineSchemaManager()
                ->listTableIndexes('users');
            
            if (!isset($indexes['users_pricing_plan_id_index']) && Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->index('pricing_plan_id');
            }
            
            if (!isset($indexes['users_account_balance_is_active_index'])) {
                if (Schema::hasColumn('users', 'account_balance') && Schema::hasColumn('users', 'is_active')) {
                    $table->index(['account_balance', 'is_active']);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les index d'abord (si ils existent)
            try {
                $table->dropIndex(['pricing_plan_id']);
            } catch (\Exception $e) {
                // Index n'existe pas, continuer
            }
            
            try {
                $table->dropIndex(['account_balance', 'is_active']);
            } catch (\Exception $e) {
                // Index n'existe pas, continuer
            }
            
            // Supprimer la contrainte de clé étrangère si elle existe
            try {
                $table->dropForeign(['pricing_plan_id']);
            } catch (\Exception $e) {
                // Contrainte n'existe pas, continuer
            }
            
            // Supprimer les colonnes si elles existent
            if (Schema::hasColumn('users', 'account_balance')) {
                $table->dropColumn('account_balance');
            }
            
            if (Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->dropColumn('pricing_plan_id');
            }
        });
    }
};