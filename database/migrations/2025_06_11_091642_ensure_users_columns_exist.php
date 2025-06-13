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
        // Vérifier et ajouter seulement les colonnes manquantes
        
        if (!Schema::hasColumn('users', 'pricing_plan_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('pricing_plan_id')->nullable()->after('email');
            });
        }
        
        if (!Schema::hasColumn('users', 'account_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('account_balance', 10, 2)->default(0)->after('pricing_plan_id');
            });
        }
        
        // Ajouter la contrainte de clé étrangère si la table pricing_plans existe
        if (Schema::hasTable('pricing_plans') && Schema::hasColumn('users', 'pricing_plan_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // La contrainte existe déjà ou table pricing_plans n'existe pas
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les colonnes si elles existent
        if (Schema::hasColumn('users', 'account_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('account_balance');
            });
        }
        
        if (Schema::hasColumn('users', 'pricing_plan_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['pricing_plan_id']);
                $table->dropColumn('pricing_plan_id');
            });
        }
    }
};