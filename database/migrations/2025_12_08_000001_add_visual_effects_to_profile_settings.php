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
            // Visual Effects
            $table->boolean('show_particles')->default(false)->after('animation_delay');
            $table->enum('particles_style', ['dots', 'lines', 'confetti', 'snow'])->default('dots')->after('show_particles');
            $table->string('particles_color', 7)->nullable()->after('particles_style');
            
            // Glowing border effect for cards
            $table->boolean('card_glow_enabled')->default(false)->after('card_border_color');
            $table->string('card_glow_color', 7)->nullable()->after('card_glow_enabled');
            $table->string('card_glow_color_secondary', 7)->nullable()->after('card_glow_color');
            $table->enum('card_glow_variant', ['default', 'cyan', 'purple', 'rainbow', 'primary'])->default('primary')->after('card_glow_color_secondary');
            
            // Background pattern/texture
            $table->enum('background_pattern', ['none', 'dots', 'grid', 'waves', 'noise'])->default('none')->after('background_overlay_opacity');
            $table->unsignedTinyInteger('background_pattern_opacity')->default(10)->after('background_pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_particles',
                'particles_style',
                'particles_color',
                'card_glow_enabled',
                'card_glow_color',
                'card_glow_color_secondary',
                'card_glow_variant',
                'background_pattern',
                'background_pattern_opacity',
            ]);
        });
    }
};
