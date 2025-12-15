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
        Schema::table('profile_settings', function (Blueprint $table) {
            // Glow animation duration in seconds (1-30)
            $table->unsignedTinyInteger('card_glow_duration')->default(6)->after('card_glow_variant');
            // Glow opacity (0.0 - 1.0)
            $table->decimal('card_glow_opacity', 3, 2)->default(1.00)->after('card_glow_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->dropColumn(['card_glow_duration', 'card_glow_opacity']);
        });
    }
};
