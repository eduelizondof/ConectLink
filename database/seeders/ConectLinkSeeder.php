<?php

namespace Database\Seeders;

use App\Models\CustomLink;
use App\Models\FloatingAlert;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\Profile;
use App\Models\ProfileSetting;
use App\Models\SocialLink;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\VcardSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConectLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create owner user
        $owner = User::firstOrCreate(
            ['email' => 'dubacano@example.com'],
            [
                'name' => 'Dubacano Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create employee user
        $vendedor = User::firstOrCreate(
            ['email' => 'carlos.vendedor@example.com'],
            [
                'name' => 'Carlos García',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create CEO user
        $ceo = User::firstOrCreate(
            ['email' => 'roberto.ceo@example.com'],
            [
                'name' => 'Roberto Martínez',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create organization for Dubacano
        $organization = Organization::firstOrCreate(
            ['slug' => 'dubacano'],
            [
                'user_id' => $owner->id,
                'name' => 'Dubacano',
                'type' => 'business',
                'description' => 'Frutas frescas de la mejor calidad. Peras, duraznos, uvas y manzanas directamente del campo a tu mesa.',
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Create product category
        $categoryFrutas = ProductCategory::create([
            'organization_id' => $organization->id,
            'name' => 'Frutas Frescas',
            'slug' => 'frutas-frescas',
            'description' => 'Selección premium de frutas frescas de temporada',
            'icon' => 'apple',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Create products
        $products = [
            [
                'name' => 'Peras Bartlett',
                'slug' => 'peras-bartlett',
                'short_description' => 'Peras dulces y jugosas, perfectas para postre',
                'description' => 'Nuestras peras Bartlett son cultivadas con el mayor cuidado en huertos seleccionados. Su sabor dulce y textura suave las hacen ideales para consumir frescas o en postres. Cosechadas en su punto óptimo de maduración.',
                'price' => 45.00,
                'sale_price' => 39.99,
                'currency' => 'MXN',
                'is_featured' => true,
                'whatsapp_message' => '¡Hola! Me interesa comprar Peras Bartlett. ¿Cuál es la disponibilidad?',
            ],
            [
                'name' => 'Duraznos de Temporada',
                'slug' => 'duraznos-temporada',
                'short_description' => 'Duraznos aromáticos y carnosos',
                'description' => 'Duraznos frescos de temporada con ese sabor característico que tanto nos gusta. Perfectos para comer solos, en ensaladas de frutas o para preparar deliciosos postres caseros.',
                'price' => 55.00,
                'currency' => 'MXN',
                'is_featured' => true,
                'whatsapp_message' => '¡Hola! Me interesan los Duraznos de Temporada. ¿Tienen disponibilidad?',
            ],
            [
                'name' => 'Uvas Red Globe',
                'slug' => 'uvas-red-globe',
                'short_description' => 'Uvas crujientes y refrescantes',
                'description' => 'Uvas Red Globe de gran tamaño, crujientes y con un sabor dulce equilibrado. Excelentes para snack, decoración de postres o como complemento en tablas de quesos.',
                'price' => 65.00,
                'sale_price' => 58.00,
                'currency' => 'MXN',
                'is_featured' => false,
                'whatsapp_message' => '¡Hola! Quiero información sobre las Uvas Red Globe.',
            ],
            [
                'name' => 'Manzanas Gala',
                'slug' => 'manzanas-gala',
                'short_description' => 'Manzanas crujientes con toque dulce',
                'description' => 'Las manzanas Gala son conocidas por su equilibrio perfecto entre dulzura y acidez. Su textura crujiente las hace ideales para cualquier momento del día. Ricas en fibra y vitaminas.',
                'price' => 42.00,
                'currency' => 'MXN',
                'is_featured' => true,
                'whatsapp_message' => '¡Hola! Me gustaría pedir Manzanas Gala. ¿Cuánto es el mínimo de compra?',
            ],
        ];

        $createdProducts = [];
        foreach ($products as $index => $productData) {
            $createdProducts[] = Product::create([
                'organization_id' => $organization->id,
                'category_id' => $categoryFrutas->id,
                'sort_order' => $index + 1,
                'is_available' => true,
                'is_active' => true,
                ...$productData,
            ]);
        }

        // Create product sections
        $productSections = [
            [
                'title' => 'Frutas Destacadas',
                'slug' => 'frutas-destacadas',
                'description' => 'Nuestras frutas más populares y mejor calificadas',
                'icon' => 'star',
                'sort_order' => 1,
            ],
            [
                'title' => 'Ofertas Especiales',
                'slug' => 'ofertas-especiales',
                'description' => 'Productos con descuentos y promociones especiales',
                'icon' => 'tag',
                'sort_order' => 2,
            ],
            [
                'title' => 'Temporada',
                'slug' => 'temporada',
                'description' => 'Frutas frescas de temporada',
                'icon' => 'calendar',
                'sort_order' => 3,
            ],
        ];

        $createdSections = [];
        foreach ($productSections as $sectionData) {
            $createdSections[] = ProductSection::create([
                'organization_id' => $organization->id,
                'is_active' => true,
                ...$sectionData,
            ]);
        }

        // Assign products to sections
        // Frutas Destacadas: Peras, Duraznos, Manzanas (featured products)
        $createdSections[0]->products()->attach([
            $createdProducts[0]->id => ['sort_order' => 1], // Peras
            $createdProducts[1]->id => ['sort_order' => 2], // Duraznos
            $createdProducts[3]->id => ['sort_order' => 3], // Manzanas
        ]);

        // Ofertas Especiales: Peras (on sale), Uvas (on sale)
        $createdSections[1]->products()->attach([
            $createdProducts[0]->id => ['sort_order' => 1], // Peras (on sale)
            $createdProducts[2]->id => ['sort_order' => 2], // Uvas (on sale)
        ]);

        // Temporada: Duraznos, Uvas
        $createdSections[2]->products()->attach([
            $createdProducts[1]->id => ['sort_order' => 1], // Duraznos
            $createdProducts[2]->id => ['sort_order' => 2], // Uvas
        ]);

        // =========================================
        // PRIMARY PROFILE (Organization/Company)
        // =========================================
        $primaryProfile = Profile::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'is_primary' => true,
            ],
            [
                'user_id' => $owner->id,
                'name' => 'Dubacano',
                'slug' => null, // Primary profile has no slug
                'job_title' => 'Distribuidora de Frutas',
                'slogan' => '🍎 Del campo a tu mesa, frescura garantizada 🍐',
                'bio' => 'Somos una empresa familiar dedicada a la distribución de frutas frescas de la mejor calidad. Con más de 15 años de experiencia, llevamos la frescura del campo directamente a tu hogar o negocio.',
                'is_active' => true,
            ]
        );

        // Primary profile settings - Vibrant fruit theme with visual effects
        ProfileSetting::updateOrCreate(
            ['profile_id' => $primaryProfile->id],
            [
                'background_type' => 'gradient',
                'background_color' => '#ffffff',
                'background_gradient_start' => '#fef3c7',
                'background_gradient_end' => '#dcfce7',
                'background_gradient_direction' => 'to-br',
                'background_pattern' => 'none',
                'background_pattern_opacity' => 5,
                'primary_color' => '#16a34a',
                'secondary_color' => '#ea580c',
                'text_color' => '#1f2937',
                'text_secondary_color' => '#4b5563',
                'card_style' => 'glass',
                'card_background_color' => 'rgba(255, 255, 255, 0.85)',
                'card_border_radius' => 'xl',
                'card_shadow' => true,
                'card_glow_enabled' => true, // Enable glowing border effect
                'card_glow_variant' => 'primary',
                'font_family' => 'Poppins',
                'animation_entrance' => 'slide-up',
                'animation_hover' => 'lift',
                'animation_delay' => 80,
                'show_particles' => true, // Enable particles
                'particles_style' => 'dots',
                'layout_style' => 'centered',
                'show_profile_photo' => true,
                'photo_style' => 'rounded',
                'photo_size' => 'xl',
                'social_style' => 'pills',
                'social_size' => 'md',
                'social_colored' => true,
            ]
        );

        // Primary profile social links
        $primarySocialLinks = [
            ['platform' => 'whatsapp', 'url' => 'https://wa.me/5218111234567', 'sort_order' => 1],
            ['platform' => 'instagram', 'url' => 'https://instagram.com/dubacano', 'sort_order' => 2],
            ['platform' => 'facebook', 'url' => 'https://facebook.com/dubacano', 'sort_order' => 3],
            ['platform' => 'tiktok', 'url' => 'https://tiktok.com/@dubacano', 'sort_order' => 4],
            ['platform' => 'email', 'url' => 'mailto:contacto@dubacano.com', 'sort_order' => 5],
            ['platform' => 'phone', 'url' => 'tel:+528111234567', 'label' => 'Llámanos', 'sort_order' => 6],
        ];

        foreach ($primarySocialLinks as $linkData) {
            SocialLink::create([
                'profile_id' => $primaryProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // Primary profile custom links with image customizations
        $primaryCustomLinks = [
            [
                'title' => '📦 Catálogo de Productos',
                'url' => '#catalogo',
                'description' => 'Conoce nuestra selección de frutas frescas',
                'icon' => 'package',
                'is_highlighted' => true,
                'image' => 'custom-links/zHaGFDWaY0G9iNGkyHreIwoebhcBhnkGaZNT8Y10.jpg',
                'image_position' => 'top',
                'image_shape' => 'square',
                'sort_order' => 1,
            ],
            [
                'title' => '🚚 Envíos a Domicilio',
                'url' => 'https://wa.me/5218111234567?text=Hola,%20quiero%20información%20sobre%20envíos',
                'description' => 'Entrega en menos de 24 horas',
                'icon' => 'truck',
                'button_color' => '#16a34a',
                'text_color' => '#ffffff',
                'image' => 'custom-links/1ku0PiOqXskldOdZ9UnBwxuV1YH272C71HWM2ndr.jpg',
                'image_position' => 'right',
                'image_shape' => 'circle',
                'sort_order' => 2,
            ],
            [
                'title' => '💼 Mayoreo para Negocios',
                'url' => 'https://wa.me/5218111234567?text=Hola,%20me%20interesa%20comprar%20al%20mayoreo',
                'description' => 'Precios especiales para restaurantes y comercios',
                'icon' => 'briefcase',
                'thumbnail' => 'thumbnails/mayoreo-thumb.jpg',
                'sort_order' => 3,
            ],
            [
                'title' => '📍 Nuestra Ubicación',
                'url' => 'https://maps.google.com/?q=Monterrey,NL',
                'description' => 'Visítanos en Monterrey, N.L.',
                'icon' => 'map-pin',
                'image_position' => 'left',
                'image_shape' => 'square',
                'sort_order' => 4,
            ],
        ];

        foreach ($primaryCustomLinks as $linkData) {
            CustomLink::create([
                'profile_id' => $primaryProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // Primary profile floating alerts
        FloatingAlert::create([
            'profile_id' => $primaryProfile->id,
            'title' => '🎉 ¡Oferta de Temporada!',
            'message' => 'Peras Bartlett con 15% de descuento. ¡Aprovecha mientras dure!',
            'type' => 'promo',
            'link_url' => '#catalogo',
            'link_text' => 'Ver Oferta',
            'position' => 'bottom-right',
            'animation' => 'bounce',
            'background_color' => '#16a34a',
            'text_color' => '#ffffff',
            'is_dismissible' => true,
            'show_on_load' => true,
            'delay_seconds' => 3,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Primary profile vCard
        VcardSetting::create([
            'profile_id' => $primaryProfile->id,
            'first_name' => 'Dubacano',
            'last_name' => 'Frutas',
            'organization' => 'Dubacano',
            'job_title' => 'Distribuidora de Frutas Frescas',
            'email' => 'contacto@dubacano.com',
            'phone' => '+52 811 123 4567',
            'phone_mobile' => '+52 811 123 4567',
            'address_street' => 'Av. Principal #123',
            'address_city' => 'Monterrey',
            'address_state' => 'Nuevo León',
            'address_zip' => '64000',
            'address_country' => 'México',
            'website' => 'https://conectlink.cnva.mx/dubacano',
            'notes' => 'Frutas frescas de la mejor calidad. Del campo a tu mesa.',
            'is_active' => true,
            'include_photo' => true,
        ]);

        // =========================================
        // EMPLOYEE PROFILE - VENDEDOR
        // =========================================
        $vendedorProfile = Profile::create([
            'organization_id' => $organization->id,
            'user_id' => $vendedor->id,
            'name' => 'Carlos García',
            'slug' => 'carlos-garcia',
            'job_title' => 'Asesor de Ventas',
            'slogan' => 'Tu aliado en frutas frescas 🍇',
            'bio' => 'Hola, soy Carlos. Con 5 años de experiencia en Dubacano, estoy aquí para ayudarte a encontrar las mejores frutas para ti y tu familia. ¡Contáctame para pedidos personalizados!',
            'is_primary' => false,
            'is_active' => true,
        ]);

        // Vendedor profile settings - Professional blue theme
        ProfileSetting::updateOrCreate(
            ['profile_id' => $vendedorProfile->id],
            [
                'background_type' => 'gradient',
                'background_gradient_start' => '#dbeafe',
                'background_gradient_end' => '#e0e7ff',
                'background_gradient_direction' => 'to-b',
                'primary_color' => '#2563eb',
                'secondary_color' => '#7c3aed',
                'text_color' => '#1e293b',
                'text_secondary_color' => '#64748b',
                'card_style' => 'solid',
                'card_background_color' => '#ffffff',
                'card_border_radius' => 'lg',
                'card_shadow' => true,
                'font_family' => 'Inter',
                'animation_entrance' => 'fade',
                'animation_hover' => 'lift',
                'animation_delay' => 100,
                'layout_style' => 'centered',
                'show_profile_photo' => true,
                'photo_style' => 'circle',
                'photo_size' => 'lg',
                'social_style' => 'icons',
                'social_size' => 'lg',
                'social_colored' => true,
            ]
        );

        // Vendedor social links
        $vendedorSocialLinks = [
            ['platform' => 'whatsapp', 'url' => 'https://wa.me/5218112345678', 'label' => 'Mi WhatsApp', 'sort_order' => 1],
            ['platform' => 'instagram', 'url' => 'https://instagram.com/carlosgarcia', 'sort_order' => 2],
            ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/carlosgarcia', 'sort_order' => 3],
            ['platform' => 'email', 'url' => 'mailto:carlos@dubacano.com', 'sort_order' => 4],
        ];

        foreach ($vendedorSocialLinks as $linkData) {
            SocialLink::create([
                'profile_id' => $vendedorProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // Vendedor custom links
        $vendedorCustomLinks = [
            [
                'title' => '📱 Agenda una llamada',
                'url' => 'https://wa.me/5218112345678?text=Hola%20Carlos,%20me%20gustaría%20agendar%20una%20llamada',
                'description' => 'Te contacto en menos de 1 hora',
                'is_highlighted' => true,
                'button_color' => '#2563eb',
                'text_color' => '#ffffff',
                'sort_order' => 1,
            ],
            [
                'title' => '🏢 Conoce Dubacano',
                'url' => '/dubacano',
                'description' => 'Visita el perfil de nuestra empresa',
                'icon' => 'building',
                'sort_order' => 2,
            ],
            [
                'title' => '🍎 Ver Catálogo',
                'url' => '/dubacano#catalogo',
                'description' => 'Explora nuestros productos',
                'icon' => 'shopping-bag',
                'sort_order' => 3,
            ],
        ];

        foreach ($vendedorCustomLinks as $linkData) {
            CustomLink::create([
                'profile_id' => $vendedorProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // Vendedor vCard
        VcardSetting::create([
            'profile_id' => $vendedorProfile->id,
            'first_name' => 'Carlos',
            'last_name' => 'García',
            'organization' => 'Dubacano',
            'job_title' => 'Asesor de Ventas',
            'department' => 'Ventas',
            'email' => 'carlos@dubacano.com',
            'phone_mobile' => '+52 811 234 5678',
            'address_city' => 'Monterrey',
            'address_state' => 'Nuevo León',
            'address_country' => 'México',
            'website' => 'https://link.cnva.mx/dubacano/carlos-garcia',
            'is_active' => true,
            'include_photo' => true,
        ]);

        // =========================================
        // EMPLOYEE PROFILE - CEO
        // =========================================
        $ceoProfile = Profile::create([
            'organization_id' => $organization->id,
            'user_id' => $ceo->id,
            'name' => 'Roberto Martínez',
            'slug' => 'roberto-martinez',
            'job_title' => 'CEO & Fundador',
            'slogan' => 'Construyendo el futuro de la distribución de frutas 🌱',
            'bio' => 'Fundador de Dubacano con más de 15 años de experiencia en el sector agrícola. Mi misión es llevar frutas frescas y de calidad a cada hogar mexicano, apoyando a productores locales.',
            'is_primary' => false,
            'is_active' => true,
        ]);

        // CEO profile settings - Executive dark theme with rainbow glow
        ProfileSetting::updateOrCreate(
            ['profile_id' => $ceoProfile->id],
            [
                'background_type' => 'gradient',
                'background_gradient_start' => '#1e293b',
                'background_gradient_end' => '#0f172a',
                'background_gradient_direction' => 'to-b',
                'background_pattern' => 'dots',
                'background_pattern_opacity' => 5,
                'primary_color' => '#f59e0b',
                'secondary_color' => '#f97316',
                'text_color' => '#f8fafc',
                'text_secondary_color' => '#94a3b8',
                'card_style' => 'glass',
                'card_background_color' => 'rgba(30, 41, 59, 0.8)',
                'card_border_radius' => 'xl',
                'card_shadow' => true,
                'card_border_color' => 'rgba(248, 250, 252, 0.1)',
                'card_glow_enabled' => true, // Enable glowing border
                'card_glow_variant' => 'rainbow', // Rainbow glow for premium look
                'font_family' => 'Playfair Display',
                'animation_entrance' => 'scale',
                'animation_hover' => 'glow',
                'animation_delay' => 120,
                'show_particles' => true,
                'particles_style' => 'lines', // Lines style for professional look
                'layout_style' => 'centered',
                'show_profile_photo' => true,
                'photo_style' => 'circle',
                'photo_size' => 'xl',
                'social_style' => 'buttons',
                'social_size' => 'md',
                'social_colored' => false,
            ]
        );

        // CEO social links
        $ceoSocialLinks = [
            ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/robertomartinez', 'sort_order' => 1],
            ['platform' => 'twitter', 'url' => 'https://twitter.com/robertomtz', 'sort_order' => 2],
            ['platform' => 'instagram', 'url' => 'https://instagram.com/roberto.mtz', 'sort_order' => 3],
            ['platform' => 'email', 'url' => 'mailto:roberto@dubacano.com', 'sort_order' => 4],
        ];

        foreach ($ceoSocialLinks as $linkData) {
            SocialLink::create([
                'profile_id' => $ceoProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // CEO custom links
        $ceoCustomLinks = [
            [
                'title' => '📰 Entrevista en Forbes México',
                'url' => 'https://forbes.com.mx',
                'description' => 'Cómo revolucionamos la distribución de frutas',
                'is_highlighted' => true,
                'sort_order' => 1,
            ],
            [
                'title' => '🎤 Mi charla TEDx',
                'url' => 'https://youtube.com',
                'description' => 'El futuro de la agricultura sostenible',
                'icon' => 'video',
                'sort_order' => 2,
            ],
            [
                'title' => '🏢 Conoce Dubacano',
                'url' => '/dubacano',
                'description' => 'La empresa que fundé hace 15 años',
                'icon' => 'building',
                'sort_order' => 3,
            ],
            [
                'title' => '📧 Contacto Empresarial',
                'url' => 'mailto:roberto@dubacano.com?subject=Contacto%20Empresarial',
                'description' => 'Para alianzas y colaboraciones',
                'icon' => 'mail',
                'sort_order' => 4,
            ],
        ];

        foreach ($ceoCustomLinks as $linkData) {
            CustomLink::create([
                'profile_id' => $ceoProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // CEO floating alert
        FloatingAlert::create([
            'profile_id' => $ceoProfile->id,
            'title' => '🎙️ Nuevo Podcast',
            'message' => 'Escucha mi episodio sobre emprendimiento agrícola',
            'type' => 'announcement',
            'link_url' => 'https://spotify.com',
            'link_text' => 'Escuchar',
            'position' => 'top',
            'animation' => 'slide',
            'background_color' => '#f59e0b',
            'text_color' => '#1e293b',
            'is_dismissible' => true,
            'show_on_load' => true,
            'delay_seconds' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // CEO vCard
        VcardSetting::create([
            'profile_id' => $ceoProfile->id,
            'first_name' => 'Roberto',
            'last_name' => 'Martínez',
            'prefix' => 'Lic.',
            'organization' => 'Dubacano',
            'job_title' => 'CEO & Fundador',
            'email' => 'roberto@dubacano.com',
            'email_work' => 'ceo@dubacano.com',
            'phone' => '+52 811 987 6543',
            'address_street' => 'Av. Principal #123',
            'address_city' => 'Monterrey',
            'address_state' => 'Nuevo León',
            'address_zip' => '64000',
            'address_country' => 'México',
            'website' => 'https://link.cnva.mx/dubacano/roberto-martinez',
            'notes' => 'CEO y Fundador de Dubacano. 15+ años de experiencia en distribución de frutas.',
            'is_active' => true,
            'include_photo' => true,
        ]);

        // =========================================
        // PERSONAL PROFILE - SAAMTHY (Influencer)
        // =========================================
        $saamthy = User::firstOrCreate(
            ['email' => 'saamthy@cnva.mx'],
            [
                'name' => 'Samanta',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create personal organization for Saamthy
        $saamthyOrganization = Organization::firstOrCreate(
            ['slug' => 'saamthy'],
            [
                'user_id' => $saamthy->id,
                'name' => 'Saamthy',
                'type' => 'personal',
                'description' => 'Creadora de contenido enfocada en moda, tendencias y contenido estético para TikTok e Instagram.',
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Create primary profile for personal organization
        $saamthyProfile = Profile::firstOrCreate(
            [
                'organization_id' => $saamthyOrganization->id,
                'is_primary' => true,
            ],
            [
                'user_id' => $saamthy->id,
                'name' => 'Saamthy',
                'slug' => null, // Primary profile has no slug
                'job_title' => 'Creadora de Contenido',
                'slogan' => 'Moda, tendencias y contenido estético ✨',
                'bio' => 'Soy creadora de contenido y estudio Diseño de Modas. Me enfoco en moda, tendencias y contenido estético para TikTok e Instagram. Me gusta hacer lipsyncs, outfits, baile y videos con vibra juvenil, fresca y real. Mi objetivo es conectar con la gente a través de estilo, autenticidad y una estética que se siente natural y cercana.',
                'is_active' => true,
            ]
        );

        // Saamthy profile settings - Modern, aesthetic theme
        ProfileSetting::updateOrCreate(
            ['profile_id' => $saamthyProfile->id],
            [
                'background_type' => 'gradient',
                'background_color' => '#ffffff',
                'background_gradient_start' => '#fce7f3',
                'background_gradient_end' => '#fdf2f8',
                'background_gradient_direction' => 'to-br',
                'background_pattern' => 'dots',
                'background_pattern_opacity' => 10,
                'primary_color' => '#ec4899',
                'secondary_color' => '#f472b6',
                'text_color' => '#1f2937',
                'text_secondary_color' => '#6b7280',
                'card_style' => 'glass',
                'card_background_color' => 'rgba(255, 255, 255, 0.9)',
                'card_border_radius' => 'xl',
                'card_shadow' => true,
                'card_glow_enabled' => true,
                'card_glow_variant' => 'purple',
                'font_family' => 'Inter',
                'animation_entrance' => 'fade',
                'animation_hover' => 'lift',
                'animation_delay' => 100,
                'show_particles' => true,
                'particles_style' => 'dots',
                'layout_style' => 'centered',
                'show_profile_photo' => true,
                'photo_style' => 'circle',
                'photo_size' => 'xl',
                'social_style' => 'pills',
                'social_size' => 'md',
                'social_colored' => true,
            ]
        );

        // Saamthy social links
        $saamthySocialLinks = [
            ['platform' => 'instagram', 'url' => 'https://www.instagram.com/saamthy/', 'sort_order' => 1],
            ['platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@saaminion', 'sort_order' => 2],
            ['platform' => 'email', 'url' => 'mailto:saamthy@cnva.mx', 'sort_order' => 3],
        ];

        foreach ($saamthySocialLinks as $linkData) {
            SocialLink::create([
                'profile_id' => $saamthyProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // Saamthy custom links
        $saamthyCustomLinks = [
            [
                'title' => '📸 Instagram',
                'url' => 'https://www.instagram.com/saamthy/',
                'description' => 'Sígueme para contenido de moda y estilo',
                'icon' => 'instagram',
                'is_highlighted' => true,
                'button_color' => '#ec4899',
                'text_color' => '#ffffff',
                'sort_order' => 1,
            ],
            [
                'title' => '🎵 TikTok',
                'url' => 'https://www.tiktok.com/@saaminion',
                'description' => 'Lipsyncs, outfits y contenido fresco',
                'icon' => 'music',
                'button_color' => '#000000',
                'text_color' => '#ffffff',
                'sort_order' => 2,
            ],
            [
                'title' => '💌 Colaboraciones',
                'url' => 'mailto:samthy.contacto@gmail.com?subject=Colaboración',
                'description' => 'Para marcas y colaboraciones',
                'icon' => 'mail',
                'sort_order' => 3,
            ],
        ];

        foreach ($saamthyCustomLinks as $linkData) {
            CustomLink::create([
                'profile_id' => $saamthyProfile->id,
                'is_active' => true,
                ...$linkData,
            ]);
        }

        // Saamthy vCard
        VcardSetting::create([
            'profile_id' => $saamthyProfile->id,
            'first_name' => 'Samanta',
            'last_name' => '',
            'job_title' => 'Creadora de Contenido',
            'email' => 'saamthy@cnva.mx',
            'email_work' => 'samthy.contacto@gmail.com',
            'website' => 'https://conectlink.cnva.mx/saamthy',
            'notes' => 'Creadora de contenido enfocada en moda, tendencias y contenido estético para TikTok e Instagram.',
            'is_active' => true,
            'include_photo' => true,
        ]);

        // =========================================
        // SUBSCRIPTIONS - Annual plans for all users
        // =========================================
        $empresarialPlan = SubscriptionPlan::where('slug', 'empresarial')->first();

        if ($empresarialPlan) {
            // Ensure Dubacano owner and Saamthy have active subscriptions
            $mainUsers = [
                ['user' => $owner, 'name' => 'Dubacano'],
                ['user' => $saamthy, 'name' => 'Saamthy'],
            ];

            foreach ($mainUsers as $userData) {
                $user = $userData['user'];
                $userName = $userData['name'];
                
                // Check if user already has an active subscription
                $activeSubscription = Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('ends_at')
                            ->orWhere('ends_at', '>', now());
                    })
                    ->first();

                if (!$activeSubscription) {
                    Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $empresarialPlan->id,
                        'billing_cycle' => 'annual',
                        'amount_paid' => $empresarialPlan->price_annual,
                        'currency' => $empresarialPlan->currency,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => now()->addYear(),
                        'payment_method' => 'manual',
                        'payment_reference' => 'SEED-' . strtoupper(Str::random(8)),
                        'notes' => "Suscripción demo creada por seeder para {$userName}",
                    ]);
                }
            }

            // Optional: Create subscriptions for employees (vendedor and ceo)
            $employees = [$vendedor, $ceo];
            foreach ($employees as $employee) {
                $activeSubscription = Subscription::where('user_id', $employee->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('ends_at')
                            ->orWhere('ends_at', '>', now());
                    })
                    ->first();

                if (!$activeSubscription) {
                    Subscription::create([
                        'user_id' => $employee->id,
                        'plan_id' => $empresarialPlan->id,
                        'billing_cycle' => 'annual',
                        'amount_paid' => $empresarialPlan->price_annual,
                        'currency' => $empresarialPlan->currency,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => now()->addYear(),
                        'payment_method' => 'manual',
                        'payment_reference' => 'SEED-' . strtoupper(Str::random(8)),
                        'notes' => 'Suscripción demo creada por seeder',
                    ]);
                }
            }

            $this->command->info('  - Subscriptions: Active Empresarial plans created/verified for all users');
        } else {
            $this->command->warn('  - Warning: Empresarial plan not found. Please run SubscriptionPlansSeeder first.');
        }

        $this->command->info('✅ link seeder completed!');
        $this->command->info('');
        $this->command->info('Created:');
        $this->command->info('  - Organization: Dubacano (slug: dubacano, type: business)');
        $this->command->info('  - Primary Profile: Dubacano (company profile)');
        $this->command->info('  - Employee: Carlos García (slug: carlos-garcia) - Vendedor');
        $this->command->info('  - Employee: Roberto Martínez (slug: roberto-martinez) - CEO');
        $this->command->info('  - Products: 4 (Peras, Duraznos, Uvas, Manzanas)');
        $this->command->info('  - Product Sections: 3 (Frutas Destacadas, Ofertas Especiales, Temporada)');
        $this->command->info('  - Organization: Saamthy (slug: saamthy, type: personal)');
        $this->command->info('  - Personal Profile: Saamthy - Creadora de Contenido');
        $this->command->info('');
        $this->command->info('URLs:');
        $this->command->info('  - /dubacano - Company profile');
        $this->command->info('  - /dubacano/carlos-garcia - Vendedor profile');
        $this->command->info('  - /dubacano/roberto-martinez - CEO profile');
        $this->command->info('  - /saamthy - Personal profile (Influencer)');
    }
}

