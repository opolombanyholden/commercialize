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
            // Rendre le champ password nullable puisque nous utilisons maintenant password_hash
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protected_invoice_pdfs', function (Blueprint $table) {
            // Remettre password comme requis
            $table->string('password')->nullable(false)->change();
        });
    }
};