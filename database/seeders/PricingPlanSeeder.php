<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingPlan;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Light',
                'slug' => 'light',
                'description' => 'Version de base pour petites entreprises',
                'pdf_download_price' => 500,
                'max_quotes_per_month' => 10,
                'max_invoices_per_month' => 5,
                'features' => ['devis_basique', 'factures_simples'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Version complète pour entreprises moyennes',
                'pdf_download_price' => 300,
                'max_quotes_per_month' => 50,
                'max_invoices_per_month' => 30,
                'features' => ['devis_avance', 'factures_completes', 'bons_livraison'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Version illimitée pour grandes entreprises',
                'pdf_download_price' => 100,
                'max_quotes_per_month' => null,
                'max_invoices_per_month' => null,
                'features' => ['tout_inclus', 'support_prioritaire', 'api_access'],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}