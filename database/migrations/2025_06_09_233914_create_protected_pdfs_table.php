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
        Schema::create('protected_pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique(); // Token d'accès unique
            $table->string('filename'); // Nom du fichier original
            $table->string('stored_path'); // Chemin de stockage sécurisé
            $table->string('password_hash'); // Hash du mot de passe
            $table->json('metadata')->nullable(); // Métadonnées (titre, auteur, etc.)
            $table->timestamp('expires_at'); // Date d'expiration
            $table->integer('download_count')->default(0); // Nombre de téléchargements
            $table->integer('max_downloads')->default(5); // Limite de téléchargements
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index pour performance
            $table->index(['token', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protected_pdfs');
    }
};