<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Light, Standard, Premium
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('pdf_download_price', 10, 2); // Prix par téléchargement PDF
            $table->integer('max_quotes_per_month')->nullable(); // Limite devis/mois
            $table->integer('max_invoices_per_month')->nullable(); // Limite factures/mois
            $table->json('features'); // Fonctionnalités incluses
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};