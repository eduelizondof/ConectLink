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
        Schema::create('profile_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();

            // Background settings
            $table->enum('background_type', ['solid', 'gradient', 'image'])->default('solid');
            $table->string('background_color', 7)->default('#ffffff'); // Hex color
            $table->string('background_gradient_start', 7)->nullable();
            $table->string('background_gradient_end', 7)->nullable();
            $table->enum('background_gradient_direction', ['to-b', 'to-r', 'to-br', 'to-bl'])->default('to-b');
            $table->string('background_image')->nullable();
            $table->unsignedTinyInteger('background_overlay_opacity')->default(0); // 0-100

            // Theme colors
            $table->string('primary_color', 7)->default('#3b82f6');
            $table->string('secondary_color', 7)->default('#8b5cf6');
            $table->string('text_color', 7)->default('#1f2937');
            $table->string('text_secondary_color', 7)->default('#6b7280');

            // Card styling
            $table->enum('card_style', ['solid', 'transparent', 'glass'])->default('solid');
            $table->string('card_background_color', 50)->default('#ffffff'); // Supports rgba()
            $table->enum('card_border_radius', ['none', 'sm', 'md', 'lg', 'xl', '2xl', 'full'])->default('lg');
            $table->boolean('card_shadow')->default(true);
            $table->string('card_border_color', 50)->nullable(); // Supports rgba()

            // Typography
            $table->string('font_family')->default('Inter');
            $table->enum('font_size', ['sm', 'base', 'lg'])->default('base');

            // Animations
            $table->enum('animation_entrance', ['none', 'fade', 'slide-up', 'slide-down', 'scale', 'bounce'])->default('fade');
            $table->enum('animation_hover', ['none', 'lift', 'glow', 'pulse', 'shake'])->default('lift');
            $table->unsignedSmallInteger('animation_delay')->default(100); // ms between items

            // Layout
            $table->enum('layout_style', ['centered', 'left', 'compact'])->default('centered');
            $table->boolean('show_profile_photo')->default(true);
            $table->enum('photo_style', ['circle', 'rounded', 'square'])->default('circle');
            $table->enum('photo_size', ['sm', 'md', 'lg', 'xl'])->default('lg');

            // Social links style
            $table->enum('social_style', ['icons', 'buttons', 'pills'])->default('icons');
            $table->enum('social_size', ['sm', 'md', 'lg'])->default('md');
            $table->boolean('social_colored')->default(true); // Use brand colors

            $table->timestamps();

            $table->unique('profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_settings');
    }
};

