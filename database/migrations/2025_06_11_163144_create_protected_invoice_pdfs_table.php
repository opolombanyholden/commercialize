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
        Schema::create('protected_invoice_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('filename');
            $table->string('password');
            $table->longText('pdf_content')->nullable(); // PDF stocké en base64
            $table->json('invoice_data'); // Données complètes de la facture
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('security_hash')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('notification_method')->nullable(); // email ou sms
            $table->string('client_contact')->nullable(); // contact pour recevoir le mot de passe
            $table->date('due_date'); // Date d'échéance de la facture
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'invoice_number']);
            $table->index(['user_id', 'is_paid']);
            $table->index('invoice_number');
            $table->index('security_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protected_invoice_pdfs');
    }
};