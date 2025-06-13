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
        Schema::table('protected_invoice_pdfs', function (Blueprint $table) {
            // Ajouter uniquement les colonnes qui n'existent pas
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'hash')) {
                $table->string('hash', 64)->unique()->nullable()->after('invoice_number');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'formatted_invoice_number')) {
                $table->string('formatted_invoice_number')->nullable()->after('invoice_number');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('password');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'password_expires_at')) {
                $table->timestamp('password_expires_at')->nullable()->after('password_hash');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'currency')) {
                $table->string('currency', 10)->default('FCFA')->after('total_amount');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'download_count')) {
                $table->integer('download_count')->default(0)->after('security_hash');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'last_downloaded_at')) {
                $table->timestamp('last_downloaded_at')->nullable()->after('download_count');
            }
            
            if (!Schema::hasColumn('protected_invoice_pdfs', 'payment_notes')) {
                $table->text('payment_notes')->nullable()->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protected_invoice_pdfs', function (Blueprint $table) {
            // Supprimer uniquement les colonnes qui existent
            $columnsToCheck = [
                'hash',
                'formatted_invoice_number', 
                'password_hash',
                'password_expires_at',
                'currency',
                'download_count',
                'last_downloaded_at',
                'payment_notes'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('protected_invoice_pdfs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};