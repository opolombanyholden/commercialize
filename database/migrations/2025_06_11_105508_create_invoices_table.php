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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Relation avec l'utilisateur
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('Utilisateur propriétaire de la facture');
            
            // Informations de la facture
            $table->string('invoice_number', 50)
                  ->comment('Numéro de facture');
            
            // Index unique pour éviter les doublons par utilisateur
            $table->unique(['user_id', 'invoice_number'], 'unique_invoice_per_user');
            
            // Informations client
            $table->string('client_name')
                  ->comment('Nom du client');
            $table->string('client_email')
                  ->comment('Email du client');
            $table->string('client_phone', 20)
                  ->nullable()
                  ->comment('Téléphone du client');
            $table->text('client_address')
                  ->comment('Adresse du client');
            
            // Dates importantes
            $table->date('invoice_date')
                  ->comment('Date de la facture');
            $table->date('due_date')
                  ->comment('Date d\'échéance');
            
            // Contenu de la facture
            $table->json('items')
                  ->comment('Articles de la facture au format JSON');
            
            // Montants calculés
            $table->decimal('subtotal', 15, 2)
                  ->default(0)
                  ->comment('Sous-total HT');
            $table->decimal('total_tax', 15, 2)
                  ->default(0)
                  ->comment('Total des taxes');
            $table->decimal('total', 15, 2)
                  ->default(0)
                  ->comment('Total TTC');
            
            // Notes et observations
            $table->text('notes')
                  ->nullable()
                  ->comment('Notes additionnelles');
            
            // Sécurité - Mot de passe pour accès client
            $table->string('password')
                  ->comment('Mot de passe hashé pour accès client');
            
            // Statut de la facture
            $table->enum('status', ['generated', 'paid', 'downloaded', 'cancelled'])
                  ->default('generated')
                  ->comment('Statut de la facture');
            
            // Informations de paiement
            $table->string('payment_method', 50)
                  ->nullable()
                  ->comment('Méthode de paiement utilisée');
            $table->json('payment_details')
                  ->nullable()
                  ->comment('Détails du paiement au format JSON');
            
            // Timestamps de suivi
            $table->timestamp('paid_at')
                  ->nullable()
                  ->comment('Date et heure du paiement');
            $table->timestamp('downloaded_at')
                  ->nullable()
                  ->comment('Date et heure du téléchargement');
            
            // Timestamps standards Laravel
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index(['user_id', 'invoice_date'], 'idx_user_date');
            $table->index(['user_id', 'due_date'], 'idx_user_due_date');
            $table->index(['status'], 'idx_status');
            $table->index(['invoice_date'], 'idx_invoice_date');
            $table->index(['due_date'], 'idx_due_date');
            $table->index(['client_email'], 'idx_client_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};