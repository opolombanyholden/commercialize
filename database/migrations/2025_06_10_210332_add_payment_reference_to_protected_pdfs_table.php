<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier quelles colonnes existent déjà
        $columns = collect(DB::select("SHOW COLUMNS FROM protected_pdfs"))->pluck('Field')->toArray();
        
        Schema::table('protected_pdfs', function (Blueprint $table) use ($columns) {
            // Ajouter payment_reference seulement si elle n'existe pas
            if (!in_array('payment_reference', $columns)) {
                $table->string('payment_reference')->nullable()->after('total_amount');
            }
            
            // Ajouter paid_at seulement si elle n'existe pas
            if (!in_array('paid_at', $columns)) {
                $table->timestamp('paid_at')->nullable()->after('is_paid');
            }
            
            // Ajouter security_hash seulement si elle n'existe pas
            if (!in_array('security_hash', $columns)) {
                $table->string('security_hash')->nullable()->after('total_amount');
            }
        });
        
        // Ajouter les index seulement s'ils n'existent pas
        try {
            if (in_array('payment_reference', $columns) || !in_array('payment_reference', $columns)) {
                // Vérifier si l'index existe déjà
                $indexes = DB::select("SHOW INDEX FROM protected_pdfs WHERE Key_name = 'protected_pdfs_payment_reference_index'");
                if (empty($indexes)) {
                    Schema::table('protected_pdfs', function (Blueprint $table) {
                        $table->index('payment_reference');
                    });
                }
            }
        } catch (\Exception $e) {
            // Index existe déjà ou autre erreur
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protected_pdfs', function (Blueprint $table) {
            // Supprimer l'index
            try {
                $table->dropIndex(['payment_reference']);
            } catch (\Exception $e) {
                // Index n'existe pas
            }
            
            // Supprimer les colonnes si elles existent
            $columns = collect(DB::select("SHOW COLUMNS FROM protected_pdfs"))->pluck('Field')->toArray();
            
            if (in_array('payment_reference', $columns)) {
                $table->dropColumn('payment_reference');
            }
            
            if (in_array('paid_at', $columns)) {
                $table->dropColumn('paid_at');
            }
            
            if (in_array('security_hash', $columns)) {
                $table->dropColumn('security_hash');
            }
        });
    }
};