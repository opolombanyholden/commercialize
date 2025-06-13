<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PricingPlan;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer le plan Light s'il n'existe pas
        $lightPlan = PricingPlan::firstOrCreate(
            ['slug' => 'light'],
            [
                'name' => 'Light',
                'description' => 'Plan de base pour débuter',
                'pdf_download_price' => 500,
                'max_quotes_per_month' => 10,
                'max_invoices_per_month' => 5,
                'features' => ['devis_basique', 'factures_simples'],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Créer un utilisateur de test
        User::firstOrCreate(
            ['email' => 'test@commercialize.com'],
            [
                'name' => 'Utilisateur Test',
                'company_name' => 'Test Company',
                'phone' => '+241 00 00 00 00',
                'address' => '123 Rue Test',
                'city' => 'Libreville',
                'postal_code' => '00000',
                'country' => 'Gabon',
                'version' => 'light',
                'password' => Hash::make('password'),
                'pricing_plan_id' => $lightPlan->id,
                'account_balance' => 2000, // 2000 FCFA de crédit initial
                'is_active' => true,
            ]
        );
    }
}