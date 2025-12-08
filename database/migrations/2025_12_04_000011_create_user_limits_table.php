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
        Schema::create('user_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('max_organizations')->default(1);
            $table->unsignedInteger('max_profiles_per_org')->default(5);
            $table->unsignedInteger('max_products_per_org')->default(20);
            $table->unsignedInteger('max_custom_links_per_profile')->default(10);
            $table->unsignedInteger('max_social_links_per_profile')->default(15);
            $table->unsignedInteger('max_alerts_per_profile')->default(3);
            $table->boolean('can_use_custom_domain')->default(false);
            $table->boolean('can_remove_branding')->default(false);
            $table->boolean('can_access_analytics')->default(true);
            $table->boolean('can_upload_images')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_limits');
    }
};





