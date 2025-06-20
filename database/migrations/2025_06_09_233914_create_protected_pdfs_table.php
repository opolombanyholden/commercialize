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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('quote_number')->unique();
            $table->string('filename');
            $table->string('password');
            $table->longText('pdf_content'); // PDF stocké en base64
            $table->json('quote_data'); // Données complètes du devis
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('security_hash')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'quote_number']);
            $table->index(['user_id', 'is_paid']);
            $table->index('quote_number');
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