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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Pro, Enterprise
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 8, 2); // 1.00 USD
            $table->decimal('price_quarterly', 8, 2); // 2.50 USD
            $table->decimal('price_semiannual', 8, 2); // 5.00 USD
            $table->decimal('price_annual', 8, 2); // 10.00 USD
            $table->string('currency', 3)->default('USD');

            // Limits included in plan
            $table->unsignedInteger('max_organizations')->default(1);
            $table->unsignedInteger('max_profiles_per_org')->default(5);
            $table->unsignedInteger('max_products_per_org')->default(20);
            $table->unsignedInteger('max_custom_links_per_profile')->default(10);
            $table->unsignedInteger('max_social_links_per_profile')->default(15);
            $table->unsignedInteger('max_alerts_per_profile')->default(3);

            // Features
            $table->boolean('can_use_custom_domain')->default(false);
            $table->boolean('can_remove_branding')->default(false);
            $table->boolean('can_access_analytics')->default(true);
            $table->boolean('can_upload_images')->default(true);
            $table->json('features')->nullable(); // Additional features list

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};





