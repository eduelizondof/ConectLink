<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Básico',
                'slug' => 'basico',
                'description' => 'Ideal para profesionales independientes',
                'price_monthly' => 1.00,
                'price_quarterly' => 2.50,
                'price_semiannual' => 5.00,
                'price_annual' => 10.00,
                'currency' => 'USD',
                'max_organizations' => 1,
                'max_profiles_per_org' => 1,
                'max_products_per_org' => 10,
                'max_custom_links_per_profile' => 5,
                'max_social_links_per_profile' => 10,
                'max_alerts_per_profile' => 1,
                'can_use_custom_domain' => false,
                'can_remove_branding' => false,
                'can_access_analytics' => true,
                'can_upload_images' => true,
                'features' => json_encode([
                    'Perfil personalizado',
                    'Código QR',
                    'Estadísticas básicas',
                    'Soporte por email',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Profesional',
                'slug' => 'profesional',
                'description' => 'Para negocios en crecimiento',
                'price_monthly' => 3.00,
                'price_quarterly' => 7.50,
                'price_semiannual' => 15.00,
                'price_annual' => 30.00,
                'currency' => 'USD',
                'max_organizations' => 1,
                'max_profiles_per_org' => 5,
                'max_products_per_org' => 50,
                'max_custom_links_per_profile' => 15,
                'max_social_links_per_profile' => 20,
                'max_alerts_per_profile' => 3,
                'can_use_custom_domain' => false,
                'can_remove_branding' => true,
                'can_access_analytics' => true,
                'can_upload_images' => true,
                'features' => json_encode([
                    'Todo en Básico',
                    'Múltiples perfiles',
                    'Catálogo de productos',
                    'Sin marca ConectLink',
                    'Alertas promocionales',
                    'Soporte prioritario',
                ]),
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Empresarial',
                'slug' => 'empresarial',
                'description' => 'Para equipos y empresas',
                'price_monthly' => 10.00,
                'price_quarterly' => 25.00,
                'price_semiannual' => 50.00,
                'price_annual' => 100.00,
                'currency' => 'USD',
                'max_organizations' => 3,
                'max_profiles_per_org' => 20,
                'max_products_per_org' => 200,
                'max_custom_links_per_profile' => 30,
                'max_social_links_per_profile' => 25,
                'max_alerts_per_profile' => 5,
                'can_use_custom_domain' => true,
                'can_remove_branding' => true,
                'can_access_analytics' => true,
                'can_upload_images' => true,
                'features' => json_encode([
                    'Todo en Profesional',
                    'Múltiples organizaciones',
                    'Dominio personalizado',
                    'API de integración',
                    'Estadísticas avanzadas',
                    'Soporte 24/7',
                    'Onboarding personalizado',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('✅ Planes de suscripción creados:');
        $this->command->info('  - Básico: $1/mes');
        $this->command->info('  - Profesional: $3/mes (Más popular)');
        $this->command->info('  - Empresarial: $10/mes');
    }
}



